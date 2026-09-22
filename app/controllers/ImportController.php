<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Contact;

class ImportController extends Controller
{
    public function form(): void
    {
        $this->render('contacts.import', [
            'pageTitle' => __('contacts.import'),
        ]);
    }

    public function run(): void
    {
        $file = $_FILES['import_file'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = __('import.no_file');
            $this->redirect('/contacts/import');
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'vcf'], true)) {
            $_SESSION['flash_error'] = __('import.invalid_type');
            $this->redirect('/contacts/import');
            return;
        }

        $rows = $ext === 'vcf'
            ? $this->parseVcf($file['tmp_name'])
            : $this->parseCsv($file['tmp_name']);

        if (empty($rows)) {
            $_SESSION['flash_error'] = __('import.empty_file');
            $this->redirect('/contacts/import');
            return;
        }

        $model    = new Contact();
        $existing = $this->existingEmails($model);

        $imported   = 0;
        $duplicates = 0;
        $skipped    = 0;

        foreach ($rows as $row) {
            if (empty($row['name'])) { $skipped++; continue; }

            $email = strtolower(trim($row['email'] ?? ''));
            if ($email && isset($existing[$email])) { $duplicates++; continue; }

            $model->create([
                'name'     => $row['name'],
                'email'    => $row['email']    ?? '',
                'phone'    => $row['phone']    ?? '',
                'company'  => $row['company']  ?? '',
                'notes'    => $row['notes']    ?? '',
                'address1' => $row['address1'] ?? '',
                'address2' => $row['address2'] ?? '',
                'city'     => $row['city']     ?? '',
                'state'    => $row['state']    ?? '',
                'zip'      => $row['zip']      ?? '',
                'country'  => $row['country']  ?? '',
            ]);

            if ($email) $existing[$email] = true;
            $imported++;
        }

        $parts = [];
        if ($imported)   $parts[] = __('import.imported',   ['n' => $imported]);
        if ($duplicates) $parts[] = __('import.duplicates', ['n' => $duplicates]);
        if ($skipped)    $parts[] = __('import.skipped',    ['n' => $skipped]);

        if ($imported > 0) {
            $_SESSION['flash_success'] = implode(' · ', $parts);
        } else {
            $_SESSION['flash_error'] = implode(' · ', $parts) ?: __('import.nothing');
        }

        $this->redirect('/contacts');
    }

    // ── Parsers ───────────────────────────────────────────────────────────────

    private function parseCsv(string $path): array
    {
        $rows    = [];
        $handle  = fopen($path, 'r');
        if (!$handle) return [];

        // Detect and strip BOM (Google CSV often has UTF-8 BOM)
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $headers = fgetcsv($handle);
        if (!$headers) { fclose($handle); return []; }

        // Normalize headers: trim + lowercase for flexible matching
        $headers = array_map('trim', $headers);

        while (($cols = fgetcsv($handle)) !== false) {
            if (count($cols) !== count($headers)) continue;
            $raw  = array_combine($headers, $cols);
            $rows[] = $this->mapCsvRow($raw);
        }

        fclose($handle);
        return $rows;
    }

    private function mapCsvRow(array $r): array
    {
        // Google Contacts CSV uses "Given Name" + "Family Name", or "Name"
        $name = trim($r['Name'] ?? '');
        if (!$name) {
            $name = trim(
                trim($r['Given Name'] ?? '') . ' ' .
                trim($r['Additional Name'] ?? '') . ' ' .
                trim($r['Family Name'] ?? '')
            );
        }

        return [
            'name'     => $name,
            'email'    => $r['E-mail 1 - Value']      ?? $r['Email']   ?? $r['email']   ?? '',
            'phone'    => $r['Phone 1 - Value']        ?? $r['Phone']   ?? $r['phone']   ?? '',
            'company'  => $r['Organization 1 - Name']  ?? $r['Company'] ?? $r['company'] ?? '',
            'address1' => $r['Address 1 - Street']     ?? $r['Address'] ?? $r['address'] ?? '',
            'address2' => $r['Address 1 - Extended Address'] ?? '',
            'city'     => $r['Address 1 - City']       ?? $r['City']    ?? $r['city']    ?? '',
            'state'    => $r['Address 1 - Region']     ?? $r['State']   ?? $r['state']   ?? '',
            'zip'      => $r['Address 1 - Postal Code']?? $r['Zip']     ?? $r['zip']     ?? '',
            'country'  => $r['Address 1 - Country']    ?? $r['Country'] ?? $r['country'] ?? '',
            'notes'    => $r['Notes']                  ?? $r['notes']   ?? '',
        ];
    }

    private function parseVcf(string $path): array
    {
        $content = file_get_contents($path);
        if (!$content) return [];

        // Unfold wrapped lines (RFC 6350 §3.2)
        $content = preg_replace("/\r\n[ \t]/", '', $content);
        $content = preg_replace("/\r\n/", "\n", $content);

        $contacts = [];
        $blocks   = preg_split('/BEGIN:VCARD/i', $content);

        foreach ($blocks as $block) {
            if (stripos($block, 'END:VCARD') === false) continue;
            $contacts[] = $this->mapVcfBlock($block);
        }

        return $contacts;
    }

    private function mapVcfBlock(string $block): array
    {
        $get = function (string $prop) use ($block): string {
            // Match PROP;params:value or PROP:value
            if (preg_match('/^' . $prop . '[;:][^\n]*:([^\n]*)/im', $block, $m))
                return trim($m[1]);
            if (preg_match('/^' . $prop . ':([^\n]*)/im', $block, $m))
                return trim($m[1]);
            return '';
        };

        // Name: FN is the formatted full name, prefer it over N
        $name = $get('FN');
        if (!$name) {
            // N field: Family;Given;Additional;Prefix;Suffix
            $n = $get('N');
            if ($n) {
                $parts = explode(';', $n);
                $name  = trim(($parts[1] ?? '') . ' ' . ($parts[0] ?? ''));
            }
        }

        // Email — may have TYPE param: EMAIL;TYPE=INTERNET:...
        $email = '';
        if (preg_match('/^EMAIL[;:][^\n]*:([^\n]+)/im', $block, $m)) {
            $email = trim($m[1]);
        }

        // Phone
        $phone = '';
        if (preg_match('/^TEL[;:][^\n]*:([^\n]+)/im', $block, $m)) {
            $phone = trim($m[1]);
        }

        // Organization
        $org  = $get('ORG');
        $org  = explode(';', $org)[0]; // first segment is company name

        // Address: ADR field — ;PO Box;Extended;Street;City;Region;PostalCode;Country
        $adr  = '';
        if (preg_match('/^ADR[;:][^\n]*:([^\n]+)/im', $block, $m)) {
            $adr = trim($m[1]);
        }
        $adrParts = array_pad(explode(';', $adr), 7, '');

        // Notes
        $notes = $get('NOTE');
        // Decode quoted-printable if needed
        if (stripos($block, 'ENCODING=QUOTED-PRINTABLE') !== false) {
            $notes = quoted_printable_decode($notes);
        }

        return [
            'name'     => $name,
            'email'    => $email,
            'phone'    => $phone,
            'company'  => trim($org),
            'address1' => trim($adrParts[2]),
            'address2' => trim($adrParts[1]),
            'city'     => trim($adrParts[3]),
            'state'    => trim($adrParts[4]),
            'zip'      => trim($adrParts[5]),
            'country'  => trim($adrParts[6]),
            'notes'    => $notes,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function existingEmails(Contact $model): array
    {
        $map = [];
        foreach ($model->all() as $c) {
            if (!empty($c['email'])) $map[strtolower($c['email'])] = true;
        }
        return $map;
    }
}
