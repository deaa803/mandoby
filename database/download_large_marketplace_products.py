from __future__ import annotations

import argparse
import csv
import hashlib
import html
import json
import re
import shutil
import sys
import time
from collections import Counter, defaultdict
from io import BytesIO
from pathlib import Path
from typing import Any, Iterable

from datasets import load_dataset
from PIL import Image, ImageDraw, ImageFile

ImageFile.LOAD_TRUNCATED_IMAGES = True

DATASET_NAME = "Shopify/product-catalogue"
IMPORT_DIR_NAME = "large_marketplace_1000"
PRODUCTS_PER_COMPANY = 20
MAX_RETRIES = 5

CATEGORY_NAMES_AR = {
    "Home & Garden": "منزل وحديقة",
    "Apparel & Accessories": "ألبسة وإكسسوارات",
    "Electronics": "إلكترونيات",
    "Baby & Toddler": "أطفال",
    "Furniture": "أثاث",
    "Toys & Games": "ألعاب",
    "Office Supplies": "قرطاسية",
    "Cameras & Optics": "كاميرات وبصريات",
    "Animals & Pet Supplies": "مستلزمات الحيوانات",
    "Sporting Goods": "رياضة ولياقة بدنية",
    "Health & Beauty": "عناية شخصية وتجميل",
    "Media": "كتب ومجلات",
    "Hardware": "معدات وأدوات صناعية",
    "Food, Beverages & Tobacco": "أطعمة ومشروبات",
    "Arts & Entertainment": "هوايات وفنون",
}

BLOCKED_TERMS = {
    "alcohol",
    "alcoholic",
    "beer",
    "wine",
    "spirits",
    "tobacco",
    "cigar",
    "cigarette",
    "vape",
    "weapon",
    "firearm",
    "gun",
    "ammunition",
    "prescription",
    "cannabis",
    "marijuana",
    "thc",
    "cbd",
}

BASE_PRICES = {
    "منزل وحديقة": 35000,
    "ألبسة وإكسسوارات": 45000,
    "إلكترونيات": 120000,
    "أطفال": 30000,
    "أثاث": 180000,
    "ألعاب": 25000,
    "قرطاسية": 8000,
    "كاميرات وبصريات": 150000,
    "مستلزمات الحيوانات": 22000,
    "رياضة ولياقة بدنية": 65000,
    "عناية شخصية وتجميل": 28000,
    "كتب ومجلات": 18000,
    "معدات وأدوات صناعية": 95000,
    "أطعمة ومشروبات": 12000,
    "هوايات وفنون": 30000,
}

PACKAGE_WEIGHT_RANGES = {
    "منزل وحديقة": (0.250, 15.000),
    "ألبسة وإكسسوارات": (0.100, 3.000),
    "إلكترونيات": (0.080, 12.000),
    "أطفال": (0.100, 10.000),
    "أثاث": (5.000, 90.000),
    "ألعاب": (0.100, 10.000),
    "قرطاسية": (0.050, 12.000),
    "كاميرات وبصريات": (0.100, 8.000),
    "مستلزمات الحيوانات": (0.200, 25.000),
    "رياضة ولياقة بدنية": (0.200, 40.000),
    "عناية شخصية وتجميل": (0.050, 4.000),
    "كتب ومجلات": (0.100, 5.000),
    "معدات وأدوات صناعية": (0.200, 50.000),
    "أطعمة ومشروبات": (0.100, 25.000),
    "هوايات وفنون": (0.050, 15.000),
}

SPECIALTY_KEYWORDS = {
    "C01": ("kitchen", "cook", "bake", "dish", "utensil", "tableware"),
    "C02": ("garden", "plant", "lawn", "outdoor", "watering", "soil"),
    "C03": ("decor", "storage", "organizer", "curtain", "rug", "mirror"),
    "C04": ("women", "woman", "female", "dress", "skirt", "blouse"),
    "C05": ("men", "man", "male", "shirt", "trouser", "suit"),
    "C06": ("shoe", "bag", "wallet", "belt", "accessory", "jewelry"),
    "C07": ("phone", "mobile", "smartphone", "charger", "case", "tablet"),
    "C08": ("computer", "laptop", "keyboard", "mouse", "network", "router"),
    "C09": ("audio", "speaker", "headphone", "smart home", "microphone"),
    "C10": ("power", "battery", "cable", "adapter", "charger", "portable"),
    "C11": ("baby", "infant", "feeding", "diaper", "bottle", "stroller"),
    "C12": ("kids clothing", "children clothing", "baby clothing", "kids shoes"),
    "C13": ("nursery", "baby care", "safety", "bath", "monitor", "carrier"),
    "C14": ("sofa", "living room", "coffee table", "armchair", "tv stand"),
    "C15": ("office furniture", "desk", "office chair", "filing cabinet"),
    "C16": ("bed", "bedroom", "wardrobe", "dresser", "mattress", "storage"),
    "C17": ("educational", "learning", "science", "stem", "activity"),
    "C18": ("outdoor toy", "ride-on", "sports toy", "active", "playground"),
    "C19": ("puzzle", "board game", "card game", "strategy", "party game"),
    "C20": ("school", "notebook", "pencil", "pen", "backpack", "student"),
    "C21": ("office", "paper", "folder", "stapler", "printer", "desk"),
    "C22": ("paint", "marker", "crayon", "craft", "drawing", "art supply"),
    "C23": ("camera", "lens", "dslr", "mirrorless", "camcorder"),
    "C24": ("binocular", "telescope", "optical", "scope", "magnifier"),
    "C25": ("lighting", "tripod", "flash", "studio", "camera accessory"),
    "C26": ("pet food", "dog food", "cat food", "treat", "feed"),
    "C27": ("pet bed", "cage", "aquarium", "collar", "leash", "habitat"),
    "C28": ("grooming", "shampoo", "litter", "cleaning", "brush"),
    "C29": ("fitness", "gym", "treadmill", "dumbbell", "exercise", "yoga"),
    "C30": ("sportswear", "jersey", "training", "sport accessory", "athletic"),
    "C31": ("bicycle", "bike", "cycling", "helmet", "scooter"),
    "C32": ("skin", "face", "cream", "serum", "cleanser", "moisturizer"),
    "C33": ("perfume", "fragrance", "body", "deodorant", "bath"),
    "C34": ("makeup", "cosmetic", "lipstick", "mascara", "brush", "nail"),
    "C35": ("fiction", "literature", "novel", "history", "philosophy", "book"),
    "C36": ("science", "technology", "business", "management", "computer book"),
    "C37": ("children book", "education", "school book", "learning book"),
    "C38": ("power tool", "drill", "saw", "grinder", "electric tool"),
    "C39": ("hand tool", "wrench", "screwdriver", "repair", "maintenance"),
    "C40": ("safety", "protective", "helmet", "glove", "workwear", "industrial"),
    "C41": ("canned", "grocery", "rice", "pasta", "sauce", "pantry"),
    "C42": ("candy", "chocolate", "cookie", "cake", "bakery", "sweet"),
    "C43": ("juice", "drink", "beverage", "water", "coffee", "tea"),
    "C44": ("organic", "natural", "healthy", "gluten free", "vegan", "snack"),
    "C45": ("paint", "canvas", "drawing", "color", "artist", "sketch"),
    "C46": ("music", "guitar", "piano", "instrument", "drum", "microphone"),
    "C47": ("craft", "model", "sewing", "knitting", "handmade", "hobby"),
    "C48": ("cleaning", "organizer", "laundry", "vacuum", "mop", "storage"),
    "C49": ("watch", "jewelry", "bracelet", "necklace", "accessory"),
    "C50": ("hair", "shampoo", "conditioner", "styling", "dryer", "comb"),
}

FIELDNAMES = [
    "product_code",
    "company_email",
    "category",
    "name",
    "description",
    "status",
    "price",
    "min_order_quantity",
    "package_weight_kg",
    "image_url",
    "discount_quantity",
    "discount_percentage",
    "features_json",
]


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Download 1000 Shopify products and images into Laravel public storage."
    )
    parser.add_argument(
        "--fresh",
        action="store_true",
        help="Delete the previous generated dataset and start again.",
    )
    parser.add_argument(
        "--max-retries",
        type=int,
        default=MAX_RETRIES,
        help="How many times to reconnect if the stream is interrupted.",
    )
    return parser.parse_args()


def project_paths() -> tuple[Path, Path, Path, Path, Path]:
    database_dir = Path(__file__).resolve().parent
    project_root = database_dir.parent
    companies_csv = database_dir / "data" / "large_marketplace" / "companies.csv"
    import_root = (
        project_root
        / "storage"
        / "app"
        / "public"
        / "imports"
        / IMPORT_DIR_NAME
    )
    return (
        companies_csv,
        import_root,
        import_root / "images",
        import_root / "company_logos",
        import_root / "products.csv",
    )


def clean_text(value: Any, max_length: int) -> str:
    text = "" if value is None else str(value)
    text = html.unescape(text)
    text = re.sub(r"<[^>]+>", " ", text)
    text = re.sub(r"\s+", " ", text).strip()
    return text[:max_length]


def contains_blocked_term(*values: str) -> bool:
    combined = " ".join(values).casefold()
    return any(term in combined for term in BLOCKED_TERMS)


def classify_category(category_path: str) -> str | None:
    parts = [part.strip() for part in category_path.split(" > ") if part.strip()]
    if not parts:
        return None

    top_category = parts[0]
    lowered = category_path.casefold()

    if top_category == "Home & Garden" and any(
        term in lowered
        for term in (
            "furniture",
            "chair",
            "table",
            "sofa",
            "bed",
            "cabinet",
            "shelving",
            "desk",
            "mattress",
        )
    ):
        return "أثاث"

    if top_category == "Media" and not any(
        term in lowered for term in ("book", "magazine", "publication")
    ):
        return None

    return CATEGORY_NAMES_AR.get(top_category)


def read_companies(companies_csv: Path) -> list[dict[str, str]]:
    if not companies_csv.is_file():
        raise FileNotFoundError(f"Companies file not found: {companies_csv}")

    with companies_csv.open("r", encoding="utf-8-sig", newline="") as csv_file:
        companies = list(csv.DictReader(csv_file))

    if len(companies) != 50:
        raise RuntimeError("companies.csv must contain exactly 50 companies.")

    supported = set(CATEGORY_NAMES_AR.values())
    unsupported = sorted(
        {row.get("category", "") for row in companies if row.get("category") not in supported}
    )
    if unsupported:
        raise RuntimeError(f"Unsupported company categories: {', '.join(unsupported)}")

    return companies


def prepare_output(
    import_root: Path,
    images_dir: Path,
    logos_dir: Path,
    fresh: bool,
) -> None:
    if fresh and import_root.exists():
        shutil.rmtree(import_root)

    images_dir.mkdir(parents=True, exist_ok=True)
    logos_dir.mkdir(parents=True, exist_ok=True)


def generate_company_logos(
    companies: list[dict[str, str]],
    logos_dir: Path,
) -> None:
    for index, company in enumerate(companies, start=1):
        destination = logos_dir / f"company_{index:03d}.jpg"
        if destination.exists():
            continue

        digest = hashlib.sha256(company["email"].encode("utf-8")).digest()
        background = tuple(55 + (value % 150) for value in digest[:3])
        image = Image.new("RGB", (512, 512), background)
        draw = ImageDraw.Draw(image)
        code = company.get("company_code") or f"C{index:02d}"
        box = draw.textbbox((0, 0), code)
        width = box[2] - box[0]
        height = box[3] - box[1]
        draw.text(
            ((512 - width) / 2, (512 - height) / 2),
            code,
            fill=(255, 255, 255),
        )
        image.save(destination, format="JPEG", quality=88, optimize=True)


def normalize_image(image: Any) -> Image.Image:
    if isinstance(image, Image.Image):
        return image

    if isinstance(image, dict):
        raw_bytes = image.get("bytes")
        raw_path = image.get("path")

        if raw_bytes:
            return Image.open(BytesIO(raw_bytes))
        if raw_path:
            return Image.open(raw_path)

    raise TypeError("product_image was not decoded as a PIL image")


def save_product_image(image: Any, destination: Path) -> None:
    temp_path = destination.with_suffix(".tmp")
    converted = normalize_image(image).convert("RGB")
    converted.thumbnail((900, 900))
    converted.save(temp_path, format="JPEG", quality=85, optimize=True)
    temp_path.replace(destination)


def infer_gender(title: str, category_path: str) -> str:
    combined = f"{title} {category_path}".casefold()
    if any(word in combined for word in ("women", "woman", "female", "girls")):
        return "نساء"
    if any(word in combined for word in ("men", "man", "male", "boys")):
        return "رجال"
    if any(word in combined for word in ("baby", "toddler", "kids", "children")):
        return "أطفال"
    return "للجميع"


def package_weight_for(
    arabic_category: str,
    title: str,
    category_path: str,
) -> float:
    minimum, maximum = PACKAGE_WEIGHT_RANGES[arabic_category]
    seed = hashlib.sha256(
        f"{arabic_category}|{title}|{category_path}".encode("utf-8")
    ).digest()
    fraction = int.from_bytes(seed[:8], "big") / ((1 << 64) - 1)
    return round(minimum + ((maximum - minimum) * fraction), 3)


def build_features(
    arabic_category: str,
    category_path: str,
    brand: str,
    title: str,
    package_weight_kg: float,
) -> dict[str, str]:
    leaf_type = category_path.split(" > ")[-1].strip() or arabic_category
    brand_value = brand or "غير محددة"

    features: dict[str, str] = {
        "العلامة التجارية": brand_value,
        "اسم الشركة المصنعة": brand_value,
        "بلد المنشأ": "غير محدد",
        "النوع": leaf_type[:255],
        "حالة المنتج": "جديد",
        "الوزن": f"{package_weight_kg:.3f} كغ للطرد",
        "محتويات العبوة": "المنتج كما هو موضح في الصورة",
        "الاستخدام الموصى به": category_path[:255],
    }

    if arabic_category in {
        "ألبسة وإكسسوارات",
        "أطفال",
        "رياضة ولياقة بدنية",
    }:
        features.update(
            {
                "الجنس المستهدف": infer_gender(title, category_path),
                "الموسم": "جميع المواسم",
                "تعليمات العناية": "حسب بطاقة وتعليمات المنتج",
            }
        )

    if arabic_category == "أطفال":
        features["الفئة العمرية"] = "أطفال"

    if arabic_category == "رياضة ولياقة بدنية":
        features.update(
            {
                "نوع الرياضة": leaf_type[:255],
                "مستوى الاستخدام": "منزلي وتجاري",
            }
        )

    if arabic_category in {
        "إلكترونيات",
        "كاميرات وبصريات",
        "معدات وأدوات صناعية",
    }:
        features.update(
            {
                "الضمان": "حسب سياسة الشركة",
                "مدة الضمان": "6 أشهر",
                "مصدر الطاقة": "حسب الموديل",
                "معايير السلامة": "اتباع تعليمات الشركة المصنعة",
            }
        )

    if arabic_category == "عناية شخصية وتجميل":
        features.update(
            {
                "طريقة الاستخدام": "وفق تعليمات العبوة",
                "نوع البشرة": "حسب نوع المنتج",
                "مختبر جلدياً": "حسب بيانات الشركة المصنعة",
            }
        )

    if arabic_category == "كتب ومجلات":
        features.update({"اللغة": "الإنجليزية", "صيغة المحتوى": "مطبوع"})

    if arabic_category == "أطعمة ومشروبات":
        features.update(
            {
                "شروط التخزين": "يحفظ وفق تعليمات العبوة",
                "مدة الصلاحية": "حسب التاريخ المدون على العبوة",
                "مسببات الحساسية": "راجع مكونات العبوة",
                "حلال": "حسب بيانات العبوة",
            }
        )

    if arabic_category in {"ألعاب", "هوايات وفنون"}:
        features.update({"مستوى المهارة": "مبتدئ ومتوسط", "عدد القطع": "حسب العبوة"})

    return features


def price_for(arabic_category: str, product_number: int, title: str) -> int:
    base = BASE_PRICES[arabic_category]
    digest = int(hashlib.sha256(title.encode("utf-8")).hexdigest()[:8], 16)
    multiplier = 1 + ((digest + product_number) % 8)
    return base + (multiplier * max(1000, base // 8))


def load_existing_rows(products_csv: Path) -> list[dict[str, str]]:
    if not products_csv.is_file():
        return []

    with products_csv.open("r", encoding="utf-8-sig", newline="") as csv_file:
        return list(csv.DictReader(csv_file))


def valid_package_weight(value: str) -> bool:
    try:
        return float(value) > 0
    except (TypeError, ValueError):
        return False


def validate_existing_rows(
    rows: list[dict[str, str]],
    companies_by_email: dict[str, dict[str, str]],
    images_dir: Path,
) -> list[dict[str, str]]:
    valid_rows: list[dict[str, str]] = []
    company_counts: Counter[str] = Counter()

    for row in rows:
        email = row.get("company_email", "")
        company = companies_by_email.get(email)
        image_name = Path(row.get("image_url", "")).name

        if company is None or company["category"] != row.get("category"):
            continue
        if company_counts[email] >= PRODUCTS_PER_COMPANY:
            continue
        if not image_name or not (images_dir / image_name).is_file():
            continue
        if not valid_package_weight(row.get("package_weight_kg", "")):
            continue
        if any(field not in row for field in FIELDNAMES):
            continue

        valid_rows.append({field: row.get(field, "") for field in FIELDNAMES})
        company_counts[email] += 1

    return valid_rows


def write_rows(products_csv: Path, rows: Iterable[dict[str, str]]) -> None:
    with products_csv.open("w", encoding="utf-8-sig", newline="") as csv_file:
        writer = csv.DictWriter(csv_file, fieldnames=FIELDNAMES)
        writer.writeheader()
        writer.writerows(rows)


def specialty_score(company: dict[str, str], text: str) -> int:
    code = company.get("company_code", "")
    keywords = SPECIALTY_KEYWORDS.get(code, ())
    lowered = text.casefold()
    return sum(1 for keyword in keywords if keyword in lowered)


def choose_company(
    companies: list[dict[str, str]],
    company_counts: Counter[str],
    title: str,
    category_path: str,
) -> dict[str, str] | None:
    available = [
        company
        for company in companies
        if company_counts[company["email"]] < PRODUCTS_PER_COMPANY
    ]
    if not available:
        return None

    text = f"{title} {category_path}"
    return min(
        available,
        key=lambda company: (
            -specialty_score(company, text),
            company_counts[company["email"]],
            company["email"],
        ),
    )


def stream_rows() -> Any:
    return load_dataset(DATASET_NAME, split="train", streaming=True)


def print_missing(
    companies: list[dict[str, str]],
    company_counts: Counter[str],
) -> None:
    missing = [
        f"{company['email']}: {company_counts[company['email']]}/{PRODUCTS_PER_COMPANY}"
        for company in companies
        if company_counts[company["email"]] != PRODUCTS_PER_COMPANY
    ]
    if missing:
        print("Incomplete companies:", file=sys.stderr)
        for line in missing:
            print(f"  - {line}", file=sys.stderr)


def main() -> int:
    args = parse_args()
    if args.max_retries < 1:
        raise ValueError("--max-retries must be at least 1")

    companies_csv, import_root, images_dir, logos_dir, products_csv = project_paths()
    companies = read_companies(companies_csv)
    prepare_output(import_root, images_dir, logos_dir, args.fresh)
    generate_company_logos(companies, logos_dir)

    companies_by_email = {company["email"]: company for company in companies}
    companies_by_category: dict[str, list[dict[str, str]]] = defaultdict(list)
    for company in companies:
        companies_by_category[company["category"]].append(company)

    targets_by_category = {
        category: len(category_companies) * PRODUCTS_PER_COMPANY
        for category, category_companies in companies_by_category.items()
    }
    total_target = len(companies) * PRODUCTS_PER_COMPANY

    existing_rows = [] if args.fresh else load_existing_rows(products_csv)
    existing_rows = validate_existing_rows(existing_rows, companies_by_email, images_dir)
    write_rows(products_csv, existing_rows)

    category_counts: Counter[str] = Counter(row["category"] for row in existing_rows)
    company_counts: Counter[str] = Counter(row["company_email"] for row in existing_rows)
    seen_products = {
        (row["name"].casefold(), row["category"].casefold()) for row in existing_rows
    }
    saved = len(existing_rows)

    if saved >= total_target:
        print(f"Dataset already complete: {saved}/{total_target} products.")
        return 0

    print(f"Connecting to {DATASET_NAME}...")
    print(f"Target: {total_target} products, {PRODUCTS_PER_COMPANY} per company")
    print(f"Local output: {import_root}")
    print(f"Resuming from: {saved}/{total_target}\n")

    total_scanned = 0
    attempts_without_progress = 0

    while saved < total_target and attempts_without_progress < args.max_retries:
        before_attempt = saved

        try:
            dataset = stream_rows()

            with products_csv.open("a", encoding="utf-8-sig", newline="") as csv_file:
                writer = csv.DictWriter(csv_file, fieldnames=FIELDNAMES)

                for source_row in dataset:
                    total_scanned += 1
                    if total_scanned % 100 == 0:
                        print(f"Scanned {total_scanned} rows | Saved {saved}/{total_target}")

                    category_path = clean_text(
                        source_row.get("ground_truth_category"), 500
                    )
                    if not category_path:
                        continue

                    arabic_category = classify_category(category_path)
                    if arabic_category is None or arabic_category not in targets_by_category:
                        continue
                    if category_counts[arabic_category] >= targets_by_category[arabic_category]:
                        continue

                    title = clean_text(source_row.get("product_title"), 255)
                    description = clean_text(source_row.get("product_description"), 2000)
                    brand = clean_text(source_row.get("ground_truth_brand"), 150)

                    if not title or contains_blocked_term(title, description, category_path):
                        continue

                    dedupe_key = (title.casefold(), arabic_category.casefold())
                    if dedupe_key in seen_products:
                        continue

                    company = choose_company(
                        companies_by_category[arabic_category],
                        company_counts,
                        title,
                        category_path,
                    )
                    if company is None:
                        continue

                    image = source_row.get("product_image")
                    if image is None:
                        continue

                    product_number = saved + 1
                    image_name = f"product_{product_number:04d}.jpg"
                    image_path = images_dir / image_name

                    try:
                        save_product_image(image, image_path)
                    except Exception as error:
                        print(f"Skipped one image: {error}")
                        continue

                    package_weight_kg = package_weight_for(
                        arabic_category,
                        title,
                        category_path,
                    )
                    features = build_features(
                        arabic_category,
                        category_path,
                        brand,
                        title,
                        package_weight_kg,
                    )

                    discount_percentage = 0
                    discount_quantity = 0
                    if product_number % 6 == 0:
                        discount_percentage = 5 + ((product_number // 6) % 4) * 5
                        discount_quantity = 5 + (product_number % 5)

                    writer.writerow(
                        {
                            "product_code": f"P{product_number:04d}",
                            "company_email": company["email"],
                            "category": arabic_category,
                            "name": title,
                            "description": description
                            or f"{title} ضمن تصنيف {arabic_category}.",
                            "status": "unavailable"
                            if product_number % 19 == 0
                            else "available",
                            "price": price_for(arabic_category, product_number, title),
                            "min_order_quantity": 1 + (product_number % 6),
                            "package_weight_kg": f"{package_weight_kg:.3f}",
                            "image_url": (
                                f"imports/{IMPORT_DIR_NAME}/images/{image_name}"
                            ),
                            "discount_quantity": discount_quantity,
                            "discount_percentage": discount_percentage,
                            "features_json": json.dumps(features, ensure_ascii=False),
                        }
                    )
                    csv_file.flush()

                    seen_products.add(dedupe_key)
                    category_counts[arabic_category] += 1
                    company_counts[company["email"]] += 1
                    saved += 1

                    print(
                        f"[{saved:04d}/{total_target}] {arabic_category} "
                        f"{category_counts[arabic_category]}/"
                        f"{targets_by_category[arabic_category]} | "
                        f"{company['email']} "
                        f"{company_counts[company['email']]}/{PRODUCTS_PER_COMPANY}"
                    )

                    if saved >= total_target:
                        break

        except KeyboardInterrupt:
            print("\nStopped. Run the same command without --fresh to resume.")
            return 130
        except Exception as error:
            print(f"\nStream error: {error}", file=sys.stderr)

        if saved == before_attempt:
            attempts_without_progress += 1
        else:
            attempts_without_progress = 0

        if saved < total_target and attempts_without_progress < args.max_retries:
            print("Reconnecting in 5 seconds. Existing files will be preserved...")
            time.sleep(5)

    print("\nDONE")
    print(f"Rows scanned: {total_scanned}")
    print(f"Products saved: {saved}/{total_target}")
    print(f"Images folder: {images_dir}")
    print(f"CSV file: {products_csv}")

    if saved != total_target:
        print_missing(companies, company_counts)
        print(
            "Run the same command again without --fresh to resume.",
            file=sys.stderr,
        )
        return 1

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
