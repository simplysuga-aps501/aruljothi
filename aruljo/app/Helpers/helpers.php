<?php

if (!function_exists('formatIndianCurrency')) {
    function formatIndianCurrency($num)
    {
        if (!$num) return '0.00';
        $x = explode('.', $num);
        $lastThree = substr($x[0], -3);
        $restUnits = substr($x[0], 0, -3);
        if ($restUnits != '') {
            $lastThree = ',' . $lastThree;
        }
        $res = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits) . $lastThree;
        return '₹ ' . $res . (isset($x[1]) ? "." . $x[1] : ".00");
    }
}
