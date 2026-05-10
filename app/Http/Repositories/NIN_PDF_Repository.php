<?php

namespace App\Http\Repositories;

use App\Models\Verification;
use TCPDF;

class NIN_PDF_Repository
{
    public function regularPDF($nin_no)
    {

        if (Verification::where('idno', $nin_no)->exists()) {
            $verifiedRecord = Verification::where('idno', $nin_no)
                ->latest()
                ->first();

            $ninData = [
                'nin' => $verifiedRecord->idno,
                'fName' => $verifiedRecord->first_name,
                'sName' => $verifiedRecord->last_name,
                'mName' => $verifiedRecord->middle_name,
                'tId' => $verifiedRecord->trackingId,
                'address' => $verifiedRecord->address,
                'lga' => $verifiedRecord->lga,
                'state' => $verifiedRecord->state,
                'gender' => ($verifiedRecord->gender === 'Male') ? 'M' : 'F',
                'dob' => $verifiedRecord->dob,
                'photo' => str_replace('data:image/jpg;base64,', '', $verifiedRecord->photo),
            ];

            $names = $verifiedRecord->first_name.' '.$verifiedRecord->last_name;
            // Initialize TCPDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->setPrintHeader(false);

            // Set document information
            $pdf->SetCreator('Abu');
            $pdf->SetAuthor('Zulaiha');
            $pdf->SetTitle(html_entity_decode($names));
            $pdf->SetSubject('Regular');
            $pdf->SetKeywords('Regular, TCPDF, PHP');
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // Add a new page
            $pdf->AddPage();

            // Load the background image
            $pdf->Image(public_path('assets/card_and_Slip/regular.png'), 15, 50, 178, 80, '', '', '', false, 300, '', false, false, 0);

            // Decode and add the photo
            $photo = $ninData['photo'];
            $imgdata = base64_decode($photo);
            $pdf->Image('@'.$imgdata, 166.8, 69.3, 25, 31, '', '', '', false, 300, '', false, false, 0);

            // Add text fields using 'helvetica' font
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Text(85, 71, html_entity_decode($ninData['sName']));
            $pdf->Text(85, 79.7, html_entity_decode($ninData['fName']));
            $pdf->Text(85, 86.8, html_entity_decode($ninData['mName']));

            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text(85, 96, $ninData['gender']);

            $pdf->SetFont('helvetica', '', 7);
            $pdf->Text(32, 71.8, $ninData['tId']);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text(25, 79.5, $ninData['nin']);

            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(50, 20, html_entity_decode($ninData['address']), 0, 'L', false, 1, 116, 74, true);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text(116, 93, $ninData['lga']);
            $pdf->Text(116, 97, $ninData['state']);

            // Output the PDF
            $filename = 'Regular NIN Slip - '.$nin_no.'.pdf';
            $pdfContent = $pdf->Output($filename, 'S');

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename='.$filename)
                ->header('Content-Length', strlen($pdfContent));
        } else {

            return response()->json([
                'message' => 'Error',
                'errors' => ['Not Found' => 'Verification record not found !'],
            ], 422);
        }
    }

    public function standardPDF($nin_no)
    {

        if (Verification::where('idno', $nin_no)->exists()) {
            $verifiedRecord = Verification::where('idno', $nin_no)
                ->latest()
                ->first();

            $ninData = [
                'nin' => $verifiedRecord->idno,
                'fName' => $verifiedRecord->first_name,
                'sName' => $verifiedRecord->last_name,
                'mName' => $verifiedRecord->middle_name,
                'tId' => $verifiedRecord->trackingId,
                'address' => $verifiedRecord->address,
                'lga' => $verifiedRecord->lga,
                'state' => $verifiedRecord->state,
                'gender' => ($verifiedRecord->gender === 'Male') ? 'M' : 'F',
                'dob' => $verifiedRecord->dob,
                'photo' => str_replace('data:image/jpg;base64,', '', $verifiedRecord->photo),
            ];

            $names = $verifiedRecord->first_name.' '.$verifiedRecord->last_name;

            // Generate PDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');

            // Set document information
            $pdf->setPrintHeader(false);
            $pdf->SetCreator('Abu');
            $pdf->SetAuthor('Zulaiha');
            $pdf->SetTitle(html_entity_decode($names));
            $pdf->SetSubject('Standard');
            $pdf->SetKeywords('Standard, TCPDF, PHP');
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();
            $pdf->SetFont('dejavuserifcondensedbi', '', 12);
            $txt = "Please find below your new High Resolution NIN Slip. You may cut it out of the paper, fold and laminate as desired. Please DO NOT allow others to make copies of your NIN Slip.\n";
            $pdf->MultiCell(150, 20, $txt, 0, 'C', false, 1, 35, 20, true, 0, false, true, 0, 'T', false);

            // Add images (using JPG instead of PNG)
            $pdf->Image(public_path('assets/card_and_Slip/standard.jpg'), 70, 50, 80, 50, '', '', '', false, 300, '', false, false, 0);
            $pdf->Image(public_path('assets/card_and_Slip/back.jpg'), 70, 101, 80, 50, '', '', '', false, 300, '', false, false, 0);

            // Add QR code
            $style = [
                'border' => false,
                'padding' => 0,
                'fgcolor' => [0, 0, 0],
                'bgcolor' => [255, 255, 255],
            ];
            $datas = '{NIN: '.$ninData['nin'].', NAME:'.html_entity_decode($ninData['fName']).' '.html_entity_decode($ninData['mName']).' '.html_entity_decode($ninData['sName']).', DOB: '.$ninData['dob'].', Status:Verified}';
            $pdf->write2DBarcode($datas, 'QRCODE,H', 131.2, 64.7, 14.2, 13.5, $style, 'H');
            $pdf->Image(public_path('assets/card_and_Slip/pin.jpg'), 135.8, 69.5, 4.5, 4.5, '', '', '', false, 300, '', false, false, 0);

            // Decode the base64 image
            $photo = base64_decode($ninData['photo']);
            $pdf->Image('@'.$photo, 72, 62, 18, 23, '', '', '', false, 300, '', false, false, 0);

            // Add text fields
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text(91.5, 65, html_entity_decode($ninData['sName']));
            $pdf->Text(91.5, 72, html_entity_decode($ninData['fName']).', '.html_entity_decode($ninData['mName']));
            $newD = strtotime($ninData['dob']);
            $cdate = date('d M Y', $newD);
            $pdf->Text(91.5, 78.7, $cdate);

            $issueD = date('d M Y');
            $pdf->Text(128, 80, $issueD);

            // Add NIN
            $nin = $ninData['nin'];
            $newNin = substr($nin, 0, 4).' '.substr($nin, 4, 3).' '.substr($nin, 7);
            $pdf->SetFont('helvetica', '', 21);
            $pdf->Text(81, 89, $newNin);

            // Add watermark
            $pdf->StartTransform();
            $pdf->Rotate(50, 88, 95);
            $pdf->setTextColor(220, 220, 220);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->Text(80, 80, $nin);
            $pdf->StopTransform();

            // Output PDF
            $filename = 'Standard NIN Slip - '.$nin_no.'.pdf';
            $pdfContent = $pdf->Output($filename, 'S');

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename='.$filename);
        } else {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Not Found' => 'Verification record not found!'],
            ], 422);
        }
    }

    public function premiumPDF($nin_no)
    {
        // Check if record exists and retrieve the latest record
        if (Verification::where('idno', $nin_no)->exists()) {
            $verifiedRecord = Verification::where('idno', $nin_no)
                ->latest()
                ->first();

            // Prepare data for the PDF
            $ninData = [
                'nin' => $verifiedRecord->idno,
                'fName' => $verifiedRecord->first_name,
                'sName' => $verifiedRecord->last_name,
                'mName' => $verifiedRecord->middle_name,
                'tId' => $verifiedRecord->trackingId,
                'address' => $verifiedRecord->address,
                'lga' => $verifiedRecord->lga,
                'state' => $verifiedRecord->state,
                'gender' => ($verifiedRecord->gender === 'Male') ? 'M' : 'F',
                'dob' => $verifiedRecord->dob,
                'photo' => str_replace('data:image/jpg;base64,', '', $verifiedRecord->photo),
            ];

            $names = html_entity_decode($verifiedRecord->first_name).' '.html_entity_decode($verifiedRecord->last_name);

            // Initialize TCPDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->setPrintHeader(false);
            $pdf->SetCreator('Abu');
            $pdf->SetAuthor('Zulaiha');
            $pdf->SetTitle($names);
            $pdf->SetSubject('Premium');
            $pdf->SetKeywords('premium, TCPDF, PHP');
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();
            $pdf->SetFont('dejavuserifcondensedbi', '', 12);

            // Add text
            $txt = 'Please find below your new High Resolution NIN Slip...';
            $pdf->MultiCell(150, 20, $txt, 0, 'C', false, 1, 35, 20, true, 0, false, true, 0, 'T', false);

            // Use JPG images instead of PNG
            $pdf->Image(public_path('assets/card_and_Slip/premium.jpg'), 70, 50, 80, 50, 'JPG', '', '', false, 300, '', false, false, 0);
            $pdf->Image(public_path('assets/card_and_Slip/back.jpg'), 70, 101, 80, 50, 'JPG', '', '', false, 300, '', false, false, 0);

            // Add barcode
            $style = [
                'border' => false,
                'padding' => 0,
                'fgcolor' => [0, 0, 0],
                'bgcolor' => [255, 255, 255],
            ];
            $datas = '{NIN: '.$ninData['nin'].', NAME: '.html_entity_decode($ninData['fName']).' '.html_entity_decode($ninData['mName']).' '.html_entity_decode($ninData['sName']).', DOB: '.$ninData['dob'].', Status:Verified}';
            $pdf->write2DBarcode($datas, 'QRCODE,H', 128, 53, 20, 20, $style, 'H');

            // Add image from base64
            $photo = $ninData['photo'];
            $imgdata = base64_decode($photo);
            $pdf->Image('@'.$imgdata, 71.5, 62, 20, 25, 'JPG', '', '', false, 300, '', false, false, 0);

            // Add text
            $sur = html_entity_decode($ninData['sName']);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Text(93.3, 66.5, $sur);

            $othername = html_entity_decode($ninData['fName']).', '.html_entity_decode($ninData['mName']);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Text(93.3, 73.5, $othername);

            $dob = $ninData['dob'];
            $newD = strtotime($dob);
            $cdate = date('d M Y', $newD);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text(93.3, 80.5, $cdate);

            $gender = $ninData['gender'];
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Text(114, 80.5, $gender);

            $issueD = date('d M Y');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text(128, 81.8, $issueD);

            // Format NIN
            $nin = $ninData['nin'];
            $pdf->setTextColor(0, 0, 0);
            $newNin = substr($nin, 0, 4).' '.substr($nin, 4, 3).' '.substr($nin, 7);
            $pdf->SetFont('helvetica', '', 21);
            $pdf->Text(81, 91, $newNin);

            // Watermark
            $pdf->StartTransform();
            $pdf->Rotate(50, 88, 95);
            $pdf->setTextColor(165, 162, 156);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->Text(80, 80, $nin);
            $pdf->StopTransform();

            $pdf->StartTransform();
            $pdf->Rotate(50, 90, 95);
            $pdf->setTextColor(165, 162, 156);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->Text(77, 86, $nin);
            $pdf->StopTransform();

            $pdf->StartTransform();
            $pdf->Rotate(127, 118, 74);
            $pdf->setTextColor(165, 162, 156);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->Text(80, 80, $nin);
            $pdf->StopTransform();

            $pdf->setTextColor(165, 162, 156);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->Text(129, 73, $nin);

            // Save and download PDF

            $filename = 'Premium NIN Slip - '.$nin_no.'.pdf';
            $pdfContent = $pdf->Output($filename, 'S');

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
                ->header('Content-Length', strlen($pdfContent));
        } else {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Not Found' => 'Verification record not found!'],
            ], 422);
        }
    }

    public function extractBase64AndType($dataUrl)
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches)) {
            $type = strtoupper($matches[1]); // e.g., JPG, PNG
            $data = substr($dataUrl, strpos($dataUrl, ',') + 1);

            return [$type, $data];
        }

        return [null, $dataUrl]; // fallback
    }

    public function basicPDF($nin_no)
    {
        // Check if record exists and retrieve the latest record
        if (Verification::where('idno', $nin_no)->exists()) {
            $verifiedRecord = Verification::where('idno', $nin_no)
                ->latest()
                ->first();

            [$photoType, $photoBase64] = $this->extractBase64AndType($verifiedRecord->photo);
            [$signatureType, $signatureBase64] = $this->extractBase64AndType($verifiedRecord->signature);

            // Prepare data for the PDF
            $ninData = [
                'nin' => $verifiedRecord->idno,
                'fName' => $verifiedRecord->first_name,
                'sName' => $verifiedRecord->last_name,
                'mName' => $verifiedRecord->middle_name,
                'tId' => $verifiedRecord->trackingId,
                'phoneno' => str_replace('+234', '0', $verifiedRecord->phoneno),
                'address' => $verifiedRecord->address,
                'lga' => $verifiedRecord->lga,
                'state' => $verifiedRecord->state,
                'town' => $verifiedRecord->town,
                'residence_lga' => $verifiedRecord->residence_lga,
                'residence_state' => $verifiedRecord->residence_state,
                'residence_town' => $verifiedRecord->residence_town,
                'gender' => ($verifiedRecord->gender === 'Male') ? 'M' : 'F',
                'dob' => $verifiedRecord->dob,
                'photo' => $photoBase64,
                'photo_type' => $photoType,
                'signature' => $signatureBase64,
                'signature_type' => $signatureType,
            ];

            $names = html_entity_decode($verifiedRecord->first_name).' '.html_entity_decode($verifiedRecord->last_name);

            // Initialize TCPDF
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
            $pdf->setPrintHeader(false);
            $pdf->SetCreator('Abu');
            $pdf->SetAuthor('Zulaiha');
            $pdf->SetTitle($names);
            $pdf->SetSubject('Premium');
            $pdf->SetKeywords('premium, TCPDF, PHP');
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();
            $pdf->SetFont('dejavuserifcondensedbi', '', 12);

            // Add text
            // $txt = "Please find below your new High Resolution NIN Slip...";
            // $pdf->MultiCell(150, 20, $txt, 0, 'C', false, 1, 35, 20, true, 0, false, true, 0, 'T', false);

            // Use JPG images instead of PNG
            $pdf->Image(public_path('assets/card_and_Slip/basic.jpg'), 20, 25, 250, 163, 'JPG', '', '', false, 300, '', false, false, 0);
            // $pdf->Image('assets/card_and_Slip/back.jpg', 70, 101, 80, 50, 'JPG', '', '', false, 300, '', false, false, 0);

            // Add barcode
            $style = [
                'border' => false,
                'padding' => 0,
                'fgcolor' => [0, 0, 0],
                'bgcolor' => [255, 255, 255],
            ];
            // $datas = '{NIN: ' . $ninData['nin'] . ', NAME: ' . html_entity_decode($ninData['fName']) . ' ' . html_entity_decode($ninData['mName']) . ' ' . html_entity_decode($ninData['sName']) . ', DOB: ' . $ninData['dob'] . ', Status:Verified}';
            // $pdf->write2DBarcode($datas, 'QRCODE,H', 128, 53, 20, 20, $style, 'H');

            // Add image from base64
            $photo = $ninData['photo'];
            $imgdata = base64_decode($photo);
            $pdf->Image('@'.$imgdata, 92.1, 67.8, 46.5, 49, $ninData['photo_type'], '', '', false, 300, '', false, false, 0);

            $signature = $ninData['signature'];
            $signature = base64_decode($signature);
            $pdf->Image('@'.$signature, 109, 117.5, 30, 8, $ninData['signature_type'], '', '', false, 300, '', false, false, 0);

            // Format NIN
            $nin = $ninData['nin'];
            $pdf->setTextColor(90, 90, 90);
            $newNin = substr($nin, 0, 4).' '.substr($nin, 4, 3).' '.substr($nin, 7);
            $pdf->SetFont('helvetica', 'B', 18);
            $pdf->Text(74, 125.3, $newNin);

            $first_name = html_entity_decode($ninData['fName']);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(51, 70, $first_name);

            $midle_name = html_entity_decode($ninData['mName']);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(51, 78.5, $midle_name);

            $pdf->setTextColor(90, 90, 90);
            $sur = html_entity_decode($ninData['sName']);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(51, 92.1, $sur);

            $dob = $ninData['dob'];
            $newD = strtotime($dob);
            $cdate = date('d M Y', $newD);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(51, 101, $cdate);

            $gender = $ninData['gender'];
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(51, 116, $gender);

            $tId = $ninData['tId'];
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(51, 138.5, $tId);

            $phoneno = $ninData['phoneno'];
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(117, 138.5, $phoneno);

            $state = $ninData['state'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(51, 161, $state);

            $lga = $ninData['lga'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(117, 160.9, $lga);

            // $town = $ninData['town'];
            // $pdf->setTextColor(90, 90, 90);
            // $pdf->SetFont('helvetica', 'B', 10);
            // $pdf->Text(130, 160.9, $town);

            $residence_state = $ninData['residence_state'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(51, 149.8, $residence_state);

            $residence_lga = $ninData['residence_lga'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(117, 149.8, $residence_lga);

            $town = $ninData['town'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(130, 149.8, $town);

            $address = $ninData['address'];
            $pdf->setTextColor(90, 90, 90);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Text(47, 171, $address);

            // Save and download PDF

            $filename = 'Basic NIN Slip - '.$nin_no.'.pdf';
            $pdfContent = $pdf->Output($filename, 'S');

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
                ->header('Content-Length', strlen($pdfContent));
        } else {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Not Found' => 'Verification record not found!'],
            ], 422);
        }
    }

    public function vninPDF($nin_no)
    {
        $query = Verification::where('number_nin', $nin_no)
            ->orWhere('nin', $nin_no)
            ->orWhere('idno', $nin_no);

        if ($query->exists()) {
            $verifiedRecord = $query->latest()->first();

            $ninData = [
                "nin" => $verifiedRecord->number_nin ?? $verifiedRecord->nin ?? $verifiedRecord->idno,
                "fName" => $verifiedRecord->firstname ?? $verifiedRecord->first_name,
                "sName" => $verifiedRecord->surname ?? $verifiedRecord->last_name,
                "mName" => $verifiedRecord->middlename ?? $verifiedRecord->middle_name,
                "tId" => $verifiedRecord->trackingId,
                "address" => $verifiedRecord->residence_address ?? $verifiedRecord->address,
                "lga" => $verifiedRecord->residence_lga ?? $verifiedRecord->lga,
                "state" => $verifiedRecord->residence_state ?? $verifiedRecord->state,
                "gender" => $verifiedRecord->gender ?? 'N/A',
                "birthdate" => $verifiedRecord->birthdate ?? $verifiedRecord->dob,
                "photo" => str_replace('data:image/jpg;base64,', '', $verifiedRecord->photo_path ?? $verifiedRecord->photo),
                "created_at" => $verifiedRecord->created_at,
                "reference" => $verifiedRecord->reference,
                "agent_id" => $verifiedRecord->performed_by ?? $verifiedRecord->user_id,
            ];

            // Generate PDF - Portrait
            $slipW = 190; // Width in mm
            $slipH = 95;  // Height in mm
            $marginX = (210 - $slipW) / 2; // Center horizontally on A4
            $marginY = 30; // Top margin

            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetCreator('NIMC');
            $pdf->SetAuthor('NIMC');
            $pdf->SetTitle('VNIN Verification Slip');
            $pdf->SetSubject('VNIN Verification');
            $pdf->SetKeywords('NIMC, VNIN, Verification');
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();

            // Scale factors
            $scaleX = $slipW / 297;
            $scaleY = $slipH / 210;

            // Helper functions for coordinate mapping
            $mapX = function($x) use ($marginX, $scaleX) {
                return ($x * $scaleX) + $marginX;
            };
            
            $mapY = function($y) use ($marginY, $scaleY) {
                return ($y * $scaleY) + $marginY;
            };

            // 1. Load the background template
            $pdf->Image(public_path('assets/card_and_Slip/vnin.png'), $marginX, $marginY, $slipW, $slipH, 'PNG', '', '', false, 300, '', false, false, 0);

            // 2. Add photo (if exists)
            if (!empty($ninData['photo'])) {
                try {
                    $imgdata = base64_decode($ninData['photo']);
                    if ($imgdata !== false) {
                        $pdf->Image('@' . $imgdata, $mapX(15), $mapY(110), 20 * $scaleX, 35 * $scaleY, 'JPG', '', '', false, 300, '', false, false, 0);
                    }
                } catch (\Exception $e) {
                    // Continue without photo if there's an error
                }
            }
            
            // Format Given Names
            $givenNames = trim(($ninData['fName'] ?? '') . ' ' . ($ninData['mName'] ?? ''));

            // 3. Left QR CODE Section Details
            // Included Middle Name in QR data to consistent with "Given Names"
            $qrData = 'NIN: ' . $ninData['nin'] . 
            ', Name: ' . $ninData['sName'] . ' ' . $givenNames . 
            ', DOB: ' . ($ninData['birthdate'] ?? '');
            
            $style = [
                'border' => false,
                'padding' => 0,
                'fgcolor' => [0, 0, 0],
                'bgcolor' => [255, 255, 255]
            ];
            
            // Fixed size for square QR code
            $qrSizeLeft = 16;
            
            $pdf->write2DBarcode(
                $qrData,
                'QRCODE,M',
                $mapX(75),
                $mapY(110),
                $qrSizeLeft,
                $qrSizeLeft,
                $style,
                'N'
            );

            // 3. Left Card Section Details
            $pdf->SetFont('helvetica', '', 6); // Regular
            $pdf->Text($mapX(36), $mapY(112), strtoupper($ninData['sName'] ?? ''));

            $pdf->SetFont('helvetica', '', 6); // Regular
            $pdf->Text($mapX(36), $mapY(126), strtoupper($givenNames));

            $pdf->SetFont('helvetica', '', 6); // Regular
            if (!empty($ninData['birthdate'])) {
                $d = new \DateTime($ninData['birthdate']);
                $pdf->Text($mapX(36), $mapY(136), $d->format('d M Y'));
            }

            // 4. Middle Verification Section
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Text($mapX(103), $mapY(106), strtoupper($ninData['sName'] ?? ''));
            
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Text($mapX(103), $mapY(126), strtoupper($givenNames));

            // 6. Footer Information
            $y_row = $mapY(185);
            
            // Timestamp and Transaction ID
            $pdf->SetFont('courier', 'B', 7);
            $pdf->SetTextColor(38, 38, 38);
            
            // Use 'v' for milliseconds if PHP >= 7.3, otherwise manual substring of 'u'
            $milliseconds = substr($ninData['created_at']->format('u'), 0, 3);
            $baseString = $ninData['created_at']->format('Y-m-d\TH:i:s') . '.' . $milliseconds . ($ninData['reference'] ?? '');
            
            // Ensure the string is exactly 56 characters
            if (strlen($baseString) < 56) {
                // Pad with random hexadecimal characters
                $needed = 56 - strlen($baseString);
                $padding = substr(bin2hex(random_bytes((int)ceil($needed / 2))), 0, $needed);
                $combinedRef = $baseString . $padding;
            } else {
                // Truncate to 56 characters if it exceeds (though unlikely given normal ref lengths)
                $combinedRef = substr($baseString, 0, 56);
            }

            $pdf->Text($mapX(10), $y_row, $combinedRef);
            $pdf->SetTextColor(0, 0, 0);
            
            // Transaction Type
            $pdf->Text($mapX(155), $y_row, "TOKEN");
            
            // Verification Status
            $pdf->Text($mapX(195), $y_row, "OK");
            
            // Verification Agent ID
            $agentId = "AGT-" . substr(md5($ninData['agent_id'] ?? 'unknown'), 0, 8);
            $pdf->Text($mapX(240), $y_row, strtoupper($agentId));

            // 7. TOKEN (if exists)
            if (!empty($ninData['tId'])) {
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->Text($mapX(225), $mapY(115), "TOKEN");
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Text($mapX(225), $mapY(120), substr($ninData['tId'], 0, 15));
            }

            // 8. Watermark
            $watermarkText = "AGT-" . substr(md5($ninData['agent_id'] ?? 'unknown'), 0, 8);
            $pdf->SetFont('helvetica', 'B', 15);
            $pdf->SetTextColor(200, 200, 200);
            $pdf->SetAlpha(0.2);

            for ($i = $marginX; $i < $marginX + $slipW; $i += 60) {
                for ($j = $marginY; $j < $marginY + $slipH; $j += 40) {
                    $pdf->StartTransform();
                    $pdf->Rotate(45, $i + 15, $j + 15);
                    $pdf->Text($i, $j, $watermarkText);
                    $pdf->StopTransform();
                }
            }
            
            $pdf->SetAlpha(1);
            $pdf->SetTextColor(0, 0, 0);

            $filename = 'VNIN_Verification_Slip_' . $nin_no . '.pdf';
            $pdfContent = $pdf->Output($filename, 'S');

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($pdfContent))
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

        } else {
            return response()->json([
                "status" => "error",
                "message" => "Verification record not found!",
                "data" => null
            ], 404);
        }
    }
}

