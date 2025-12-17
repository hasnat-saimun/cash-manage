Place Bengali font files here to enable proper rendering with mPDF:

Required files (recommended):
- NotoSansBengali-Regular.ttf
- NotoSansBengali-Bold.ttf

Download from Google/Noto:
https://github.com/notofonts/bengali/releases/latest

After placing files, set in .env to use mPDF (no Chrome needed):
PDF_ENGINE=mpdf

Alternatively, to use Chrome (Browsershot) with web fonts:
PDF_ENGINE=browsershot
# Optional if Chrome is not in PATH:
# CHROME_PATH="C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe"
