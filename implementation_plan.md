### Phase 7: Nice-to-Have (OCR Feature)

#### OCR Implementation with Tesseract

**Library:** [tesseract-ocr/tesseract](https://github.com/tesseract-ocr/tesseract)

**Prerequisites:**
1. Install Tesseract binary on server:
   ```bash
   # Ubuntu/Debian
   sudo apt-get install tesseract-ocr
   sudo apt-get install tesseract-ocr-ind  # Indonesian language pack
   
   # Windows (using Chocolatey)
   choco install tesseract
   ```

2. Install PHP wrapper:
   ```bash
   composer require thiagoalessio/tesseract_ocr
   ```

**Implementation Approach:**

1. **OCR Processing:**
   - Process uploaded receipt image after compression
   - Extract text using Tesseract with Indonesian + English language
   - Parse extracted text for patterns

2. **Text Pattern Recognition:**
   - Item names: Lines with text followed by numbers
   - Prices: Number patterns with currency (Rp, IDR) or decimal
   - Quantities: Patterns like "x2", "qty: 1", numbers before items
   - Total: Keywords like "Total", "Jumlah", "Grand Total"

3. **Auto-fill Logic:**
   ```php
   use thiagoalessio\TesseractOCR\TesseractOCR;
   
   $ocr = new TesseractOCR($imagePath);
   $ocr->lang('ind', 'eng');  // Indonesian + English
   $text = $ocr->run();
   
   // Parse text to extract:
   // - Maintenance items (nama perbaikan)
   // - Prices (harga)
   // - Quantities (jumlah)
   ```

4. **User Experience:**
   - Show "Extract from Receipt" button after image upload
   - Display extracted data in review modal
   - User confirms/edits before adding to maintenance details
   - Fallback to manual entry if extraction fails

5. **Accuracy Improvements:**
   - Pre-process image: increase contrast, convert to grayscale
   - Use compressed 800px image (sufficient for OCR)
   - If accuracy < 70%, suggest manual entry

6. **Error Handling:**
   - OCR failure: Silent fallback to manual entry
   - Partial success: Fill available fields, highlight uncertain ones
   - Log extraction attempts for future training/improvement