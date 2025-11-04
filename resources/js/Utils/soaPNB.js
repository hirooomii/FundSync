import * as pdfjsLib from 'pdfjs-dist/build/pdf';
import pdfWorker from 'pdfjs-dist/build/pdf.worker.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorker;

export async function parsePDF(file) {
  const arrayBuffer = await file.arrayBuffer();
  const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;

  let fullText = '';
  for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
    const page = await pdf.getPage(pageNum);
    const content = await page.getTextContent();
    const strings = content.items.map(item => item.str.trim());
    fullText += strings.join(',') + ',';
  }

  return fullText;
}

export async function parsePNBStatement(text) {

        const result = {
            account: null,
            beginningBalance: null,
            endingBalance: null,
            transactions: []
        };

        const accountMatch = text.match(/\b\d{12}\b/);
        result.account = accountMatch ? accountMatch[0] : null;

        const beginBalanceMatch = text.match(/No,Beginning Balance,,([\d,]+\.\d{2})/);
        result.beginningBalance = beginBalanceMatch ? beginBalanceMatch[1] : null;

        const endingBalanceMatch = text.match(/Ending Balance,"This is a system-generated SOA\. No signature is required",,([\d,]+\.\d{2})/);
        result.endingBalance = endingBalanceMatch ? endingBalanceMatch[1] : null;

        const dateRangeMatch = text.match(/,([A-Za-z]+\s\d{2},\s\d{4}),([A-Za-z]+\s\d{2},\s\d{4})/);
        if (dateRangeMatch) {
        result.statementBeginningDate = dateRangeMatch[1];
        result.statementEndingDate = dateRangeMatch[2]; 
        }

      const transactionRegex = /(\d{1,3}(?:,\d{3})*\.\d{2}),(\d{2}\/\d{2}\/\d{4}),,(\d{2}\/\d{2}\/\d{4}),,([\d,]*\.\d{2}|0\.00)?,,([\d,]*\.\d{2}|0\.00)?,(?:(\d{4}),,)?([^,]+),,(\d{10})/g;
        let match;

        while ((match = transactionRegex.exec(text)) !== null) {
            const [
            _,
            balance,
            datePosted,
            valueDate,
            deposit,
            withdrawal,
            brCode,
            description,
            checkSeq
            ] = match;

            result.transactions.push({
            balance,
            datePosted,
            valueDate,
            deposit,
            withdrawal,
            brCode,
            description,
            checkSeq
            });
        }

        if (result.transactions.length > 0) {
            const first = result.transactions[0];
            const commaIndex = first.balance.indexOf(',');
            if (commaIndex !== -1) {
                first.balance = first.balance.slice(commaIndex + 1);
            }
        }
   
        return result;

    }