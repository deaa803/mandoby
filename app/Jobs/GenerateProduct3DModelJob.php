<?php

namespace App\Jobs;

use App\Models\Product3DModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GenerateProduct3DModelJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * عدد مرات محاولة تنفيذ المهمة.
     */
    public int $tries = 1;

    /**
     * مهلة Laravel Job بالثواني.
     */
    public int $timeout = 7500;

    /**
     * اعتبر المهمة فاشلة إذا تجاوزت المهلة.
     */
    public bool $failOnTimeout = true;

    public function __construct(
        public int $product3DModelId
    ) {
    }

    public function handle(): void
    {
        $model = Product3DModel::find($this->product3DModelId);

        if (!$model) {
            return;
        }

        $outputDirectory = storage_path(
            'app/product-3d/work/' . $model->id
        );

        $model->update([
            'status' => 'processing',
            'progress' => 10,
            'started_at' => now(),
            'generated_at' => null,
            'error_message' => null,
        ]);

        try {
            $projectDirectory = (string) config(
                'services.sf3d.project_dir'
            );

            $pythonExecutable = (string) config(
                'services.sf3d.python'
            );

            $runScript = (string) config(
                'services.sf3d.run_script'
            );

            $useCpu = (bool) config(
                'services.sf3d.use_cpu',
                true
            );

            $this->validateConfiguration(
                projectDirectory: $projectDirectory,
                pythonExecutable: $pythonExecutable,
                runScript: $runScript,
            );

            $sourceImage = Storage::disk('public')->path(
                $model->source_image
            );

            if (!File::exists($sourceImage)) {
                throw new RuntimeException(
                    'Source image was not found: ' . $sourceImage
                );
            }

            if (File::isDirectory($outputDirectory)) {
                File::deleteDirectory($outputDirectory);
            }

            File::ensureDirectoryExists($outputDirectory);

            $result = Process::path($projectDirectory)
                ->env([
                    'SF3D_USE_CPU' => $useCpu ? '1' : '0',
                ])
                ->timeout(7200)
                ->run([
                    $pythonExecutable,
                    $runScript,
                    $sourceImage,
                    '--output-dir',
                    $outputDirectory,
                ]);

            if ($result->failed()) {
                $processMessage = trim(
                    $result->errorOutput()
                    . PHP_EOL
                    . $result->output()
                );

                throw new RuntimeException(
                    $processMessage !== ''
                        ? $processMessage
                        : 'Stable Fast 3D process failed.'
                );
            }

            $meshFile = collect(File::allFiles($outputDirectory))
                ->first(
                    fn ($file): bool =>
                        strtolower($file->getFilename()) === 'mesh.glb'
                );

            if (!$meshFile) {
                throw new RuntimeException(
                    'Python completed, but mesh.glb was not generated.'
                );
            }

            $modelPath = sprintf(
                'product-3d/models/%d/mesh.glb',
                $model->id
            );

            $absoluteModelPath = Storage::disk('public')->path(
                $modelPath
            );

            File::ensureDirectoryExists(
                dirname($absoluteModelPath)
            );

            if (
                $model->model_file &&
                $model->model_file !== $modelPath
            ) {
                Storage::disk('public')->delete(
                    $model->model_file
                );
            }

            $copied = File::copy(
                $meshFile->getPathname(),
                $absoluteModelPath
            );

            if (!$copied) {
                throw new RuntimeException(
                    'Could not copy mesh.glb to Laravel storage.'
                );
            }

            $model->update([
                'model_file' => $modelPath,
                'status' => 'completed',
                'progress' => 100,
                'generated_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $model->update([
                'status' => 'failed',
                'progress' => 0,
                'error_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    60000
                ),
            ]);

            throw $exception;
        } finally {
            if (File::isDirectory($outputDirectory)) {
                File::deleteDirectory($outputDirectory);
            }
        }
    }

    private function validateConfiguration(
        string $projectDirectory,
        string $pythonExecutable,
        string $runScript,
    ): void {
        if ($projectDirectory === '') {
            throw new RuntimeException(
                'SF3D_PROJECT_DIR is not configured.'
            );
        }

        if ($pythonExecutable === '') {
            throw new RuntimeException(
                'SF3D_PYTHON is not configured.'
            );
        }

        if ($runScript === '') {
            throw new RuntimeException(
                'SF3D_RUN_SCRIPT is not configured.'
            );
        }

        if (!File::isDirectory($projectDirectory)) {
            throw new RuntimeException(
                'Stable Fast 3D project directory was not found: '
                . $projectDirectory
            );
        }

        if (!File::exists($pythonExecutable)) {
            throw new RuntimeException(
                'Python executable was not found: '
                . $pythonExecutable
            );
        }

        if (!File::exists($runScript)) {
            throw new RuntimeException(
                'run.py was not found: ' . $runScript
            );
        }
    }
}
