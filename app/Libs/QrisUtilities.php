<?php
namespace App\Libs;


class QrisUtilities
{
    /**
     * Convert a Static QRIS string to a Dynamic QRIS string.
     *
     * @param string $staticPayload The original static QRIS string
     * @param int $amount The transaction amount
     * @return string
     */
    public function convertToDynamic(string $staticPayload, int $amount): string
    {
        // 1. Change Initiation Method from 11 (Static) to 12 (Dynamic)
        // Replaces "010211" with "010212"
        $dynamicPayload = str_replace('010211', '010212', $staticPayload);

        // 2. Remove the Old Checksum (Last 8 characters: 6304 + 4 hex chars)
        $dynamicPayload = substr($dynamicPayload, 0, -8);

        // 3. Inject the Amount (Tag 54)
        // Format: 54 + length(2 digits) + amount
        $strAmount = (string)$amount;
        $lenAmount = str_pad(strlen($strAmount), 2, '0', STR_PAD_LEFT);
        $dynamicPayload .= "54" . $lenAmount . $strAmount;

        // 4. Recalculate the Checksum (CRC16-CCITT)
        $dynamicPayload .= "6304";
        $checksum = $this->calculateCRC16($dynamicPayload);

        return $dynamicPayload . $checksum;
    }

    /**
     * CRC16-CCITT Calculation (Polynomial 0x1021)
     */
    private function calculateCRC16(string $data): string
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
