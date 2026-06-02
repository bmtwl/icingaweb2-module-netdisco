<?php

namespace Icinga\Module\Netdisco\Mrtg;

/**
 * Render a compact SVG sparkline from MRTG counter data.
 */
class SparklineRenderer
{
    /**
     * Render an SVG sparkline.
     *
     * @param array $data List of ['in' => float, 'out' => float], oldest first
     * @param int   $width  SVG width in pixels
     * @param int   $height SVG height in pixels
     * @return string Raw SVG markup
     */
    public static function render(array $data, int $width = 90, int $height = 24): string
    {
        if (empty($data)) {
            return '';
        }

        $max = 0.0;
        foreach ($data as $row) {
            $max = max($max, $row['in'], $row['out']);
        }
        if ($max == 0) {
            $max = 1;
        }

        $count = count($data);
        $stepX = $width / max(1, $count - 1);

        $pointsIn = [];
        $pointsOut = [];
        $areaPoints = ["0,{$height}"];

        foreach ($data as $i => $row) {
            $x    = round($i * $stepX, 2);
            $yIn  = round($height - (($row['in'] / $max) * $height), 2);
            $yOut = round($height - (($row['out'] / $max) * $height), 2);

            $pointsIn[]  = "{$x},{$yIn}";
            $pointsOut[] = "{$x},{$yOut}";
            $areaPoints[] = "{$x},{$yIn}";
        }

        $areaPoints[] = "{$width},{$height}";
        $areaD = 'M' . implode(' L', $areaPoints) . ' Z';

        return sprintf(
            '<svg width="%d" height="%d" viewBox="0 0 %d %d" xmlns="http://www.w3.org/2000/svg" class="mrtg-sparkline">'
            . '<path d="%s" fill="#0095bf" fill-opacity="0.08" stroke="none" />'
            . '<polyline fill="none" stroke="#0095bf" stroke-width="1.5" points="%s" />'
            . '<polyline fill="none" stroke="#f57900" stroke-width="1.5" points="%s" stroke-opacity="0.75" />'
            . '</svg>',
            $width,
            $height,
            $width,
            $height,
            $areaD,
            implode(' ', $pointsIn),
            implode(' ', $pointsOut)
        );
    }
}
