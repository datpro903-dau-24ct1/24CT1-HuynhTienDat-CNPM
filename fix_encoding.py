from pathlib import Path

ROOT = Path("src")

# Những chuỗi đặc trưng của lỗi UTF-8 bị đọc sai thành Latin-1/Windows-1252
BAD_MARKERS = [
    "Ã", "Â", "Æ", "Ä", "á", "à", "â", "ã",
    "å", "ç", "è", "é", "ê", "ì", "í",
    "ò", "ó", "ô", "õ", "ù", "ú", "ý",
    "ð", "¤", "º", "»", "œ", "ž", "†"
]


def bad_score(text):
    """
    Đếm mức độ nghi ngờ chuỗi bị lỗi encoding.
    """
    score = 0

    for marker in BAD_MARKERS:
        score += text.count(marker)

    return score


def repair_text(text):
    """
    Thử sửa toàn bộ text nhiều lần.
    Chỉ giữ kết quả nếu mức độ mojibake giảm.
    """

    current = text

    for _ in range(3):

        before = bad_score(current)

        if before == 0:
            break

        try:
            repaired = current.encode("latin1").decode("utf-8")
        except (UnicodeEncodeError, UnicodeDecodeError):
            break

        after = bad_score(repaired)

        # Chỉ nhận kết quả nếu lỗi giảm rõ ràng
        if after < before:
            current = repaired
        else:
            break

    return current


for path in ROOT.rglob("*"):

    if not path.is_file():
        continue

    if path.suffix.lower() not in [".jsx", ".js", ".css"]:
        continue

    try:
        original = path.read_text(encoding="utf-8")
    except UnicodeDecodeError:
        print("BỎ QUA:", path)
        continue

    original_score = bad_score(original)

    if original_score == 0:
        print("KHÔNG CẦN SỬA:", path)
        continue

    repaired = repair_text(original)

    repaired_score = bad_score(repaired)

    if repaired_score < original_score:

        path.write_text(
            repaired,
            encoding="utf-8",
            newline=""
        )

        print(
            "ĐÃ SỬA:",
            path,
            "|",
            original_score,
            "->",
            repaired_score
        )

    else:

        print(
            "KHÔNG ĐỔI:",
            path,
            "| score:",
            original_score
        )