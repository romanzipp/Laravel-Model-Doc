<?php

namespace romanzipp\ModelDoc\Utils;

class StringUtils
{
    public static function detectEOL($str): string {
        static $eols = array(
            "\r\n",  // 0x0D - 0x0A - Windows, DOS OS/2
            "\n",    // 0x0A -      - Unix, OSX
            "\r",    // 0x0D -      - Apple ][, TRS80
        );

        $curCount = 0;
        $curEol = '';
        foreach($eols as $eol) {
            if( ($count = substr_count($str, $eol)) > $curCount) {
                $curCount = $count;
                $curEol = $eol;
            }
        }
        return $curEol;
    }
}
