<?php
namespace App\Helpers;

use Carbon\Carbon;

class HVero
{
	public static function spaceAfterCapitalLetters($string)
{
    return preg_replace('/(?<!\ )[A-Z]/', ' $0', $string);
    ;
}
public static function capitializeType($type)
{
    return __(self::spaceAfterCapitalLetters(camelize($type)));
}
public static function formatDateForChart(string $date):string
{
    return Carbon::make($date)->format('Y-m-d');
}
public static function getMonthOfDate(string $date)
{
    return explode('-', $date)[1];
}
public static function getYearOfDate(string $date)
{
    return explode('-', $date)[0];
}

public static function getMainMonthsForInterval(string $quarterName): array
{
    return [
        'quarterly' => [
            '01' => '03',
            '02' => '03',
            '04' => '06',
            '05' => '06',
            '07' => '09',
            '08' => '09',
            '10' => '12',
            '11' => '12'
        ],
        'semi-annually' => [
            '01' => '06',
            '02' => '06',
            '03' => '06',
            '04' => '06',
            '05' => '06',
            '07' => '12',
            '08' => '12',
            '09' => '12',
            '10' => '12',
            '11' => '12'
        ]
    ][$quarterName];
}

public static function  addLastMonthOfInterval(array $dates, string $quarterName, string $endMonthOfDate, string $endYearOfDate)
{
    $endMonthOfQuarterMonth = self::getMainMonthsForInterval($quarterName)[$endMonthOfDate];
    $formattedDate = $endYearOfDate . '-' . $endMonthOfQuarterMonth . '-' . '01';
    $dates[$formattedDate] = Carbon::make($formattedDate)->format('M\'Y');

    return $dates;
}
public static function formatDateIntervalFor(array $dates, string $quarterName)
{
    if (!in_array($quarterName, ['quarterly', 'semi-annually'])) {
        throw new \Exception(__('Not Support Quarterly Name , Only Quarterly Or Semi Annually Allowed'));
    }
    $endMonthOfDates = self::getMonthOfDate(array_key_last($dates));
    $endYearOfDates = self::getYearOfDate(array_key_last($dates));
    $mainMonthsOfInterval = self::getMainMonthsForInterval($quarterName);

    if (!in_array($endMonthOfDates, $mainMonthsOfInterval)) {
        $dates = self::addLastMonthOfInterval($dates, $quarterName, $endMonthOfDates, $endYearOfDates);
    }
    return self::removeAdditionalMonthsOfInterval($dates, $quarterName, $mainMonthsOfInterval);
}

protected static function removeAdditionalMonthsOfInterval(array $dates, string $quarterName, array $mainMonthsOfInterval)
{
    $newDates = [];
    foreach ($dates as $date => $dateFormatted) {
        if (in_array(explode('-', $date)[1], $mainMonthsOfInterval)) {
            $newDates[$date] = $dateFormatted;
        }
    }

    return $newDates;
}
// public static function getExpensesPercentageOfForSelect2():array
// {
//     return [
//         [
//             'title'=>__('Revenues'), //  interest amount [leasing , ijara]
//             'value'=>'revenue'
//         ],
//         [
//             'title'=>__('Contracts'), // monthly loan amount [leasing , mortgage ]
//             'value'=>'contract'
//         ],
//         [
//             'title'=>__('Outstanding'), // monthly end balance
//             'value'=>'outstanding'
//         ],
//         [
//             'title'=>__('Collection'), // schedule payment [leasing , ijara]
//             'value'=>'collection'
//         ]
        
//     ];
// }


public static function getDurationIntervalTypesForSelect(): array
{
    return [
        [
            'value' => 'monthly',
            'title' => __('Monthly')
        ],
        [
            'value' => 'quarterly',
            'title' => __('Quarterly')
        ],
        [
            'value' => 'semi-annually',
            'title' => __('Semi Annually')
        ],
        [
            'value' => 'annually',
            'title' => __('Annually')
        ],
    ];
}
public static function getIntervalForSelect(string $intervalName)
{
    $index = 0;
    $intervalsFormattedForSelect = self::getDurationIntervalTypesForSelect();
    foreach ($intervalsFormattedForSelect as $intervalArray) {
        if ($intervalArray['value'] != $intervalName) {
            $index++;
        }

        break;
    }

    return getArrayValuesFromIndex($intervalsFormattedForSelect, $index);
}


public static function getCustomerNature(?string $customerName, array $allDataArray)
{
    unset($allDataArray['totals']);
    foreach ($allDataArray as $key => $array) {
        foreach ($array as $type => $arr) {
            foreach ($arr as $ar) {
                if ($ar->customer_name === $customerName) {
                    return str_replace(' ', '', $type);
                }
            }
        }
    }

    return '';
}
public static function getSummaryCustomerDashboardForEachType($allFormattedWithOthers, $customerNature)
{
    $dataFormatted = [];
    foreach ($allFormattedWithOthers as $customerObject) {
        $userType = self::getCustomerNature($customerObject->customer_name, $customerNature);

        isset($dataFormatted[$userType]) ? $dataFormatted[$userType] = [
            'count' => $dataFormatted[$userType]['count'] + 1,
            'sales' => $dataFormatted[$userType]['sales'] + $customerObject->val
        ]
            : $dataFormatted[$userType] = [
                'count' => 1,
                'sales' => $customerObject->val
            ];
    }
    $dataFormatted = array_filter($dataFormatted);

    return self::array_sort_type($dataFormatted);
}
protected static function array_sort_type($array)
{
    (
        uasort(
            $array,
            function ($firstElement, $secondElement) {
                if (isset($firstElement['sales'], $secondElement['sales'])) {
                    $firstElement = $firstElement['sales'];

                    $secondElement = $secondElement['sales'];
                    if ($firstElement == $secondElement) {
                        return 0;
                    }

                    return ($firstElement > $secondElement) ? -1 : 1;
                }

                return;
            }
        )
    );

    return $array;
}

}
