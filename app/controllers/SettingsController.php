<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    private Setting $model;

    public function __construct()
    {
        $this->requireAdmin();
        $this->model = new Setting();
    }

    public function index(): void
    {
        $this->render('settings.index', [
            'pageTitle'       => __('settings.title'),
            'allCurrencies'   => $this->allCurrencies(),
            'activeCurrencies'=> $this->model->currencies(),
            'defaultCurrency' => $this->model->defaultCurrency(),
        ]);
    }

    public function update(): void
    {
        $selected = $_POST['currencies'] ?? [];

        // Validate — every submitted code must exist in the master list
        $valid = array_keys($this->allCurrencies());
        $selected = array_filter($selected, fn($c) => in_array($c, $valid, true));

        if (empty($selected)) {
            $_SESSION['flash_error'] = __('settings.currencies_required');
            $this->redirect('/settings');
            return;
        }

        $default = $_POST['default_currency'] ?? $selected[0];
        if (!in_array($default, $selected, true)) {
            $default = $selected[0];
        }

        $this->model->set('currencies',       implode(',', $selected));
        $this->model->set('default_currency', $default);

        $_SESSION['flash_success'] = __('settings.saved');
        $this->redirect('/settings');
    }

    // ISO 4217 — code => [name, symbol]
    public static function allCurrencies(): array
    {
        return [
            'AED' => ['UAE Dirham',                   'د.إ'],
            'AFN' => ['Afghan Afghani',                '؋'],
            'ALL' => ['Albanian Lek',                  'L'],
            'AMD' => ['Armenian Dram',                 '֏'],
            'ANG' => ['Netherlands Antillean Guilder', 'ƒ'],
            'AOA' => ['Angolan Kwanza',                'Kz'],
            'ARS' => ['Argentine Peso',                '$'],
            'AUD' => ['Australian Dollar',             'A$'],
            'AWG' => ['Aruban Florin',                 'ƒ'],
            'AZN' => ['Azerbaijani Manat',             '₼'],
            'BAM' => ['Bosnia-Herzegovina Mark',       'KM'],
            'BBD' => ['Barbadian Dollar',              'Bds$'],
            'BDT' => ['Bangladeshi Taka',              '৳'],
            'BGN' => ['Bulgarian Lev',                 'лв'],
            'BHD' => ['Bahraini Dinar',                'BD'],
            'BMD' => ['Bermudian Dollar',              '$'],
            'BND' => ['Brunei Dollar',                 'B$'],
            'BOB' => ['Bolivian Boliviano',            'Bs.'],
            'BRL' => ['Brazilian Real',                'R$'],
            'BSD' => ['Bahamian Dollar',               'B$'],
            'BWP' => ['Botswana Pula',                 'P'],
            'BYN' => ['Belarusian Ruble',              'Br'],
            'BZD' => ['Belize Dollar',                 'BZ$'],
            'CAD' => ['Canadian Dollar',               'CA$'],
            'CDF' => ['Congolese Franc',               'FC'],
            'CHF' => ['Swiss Franc',                   'CHF'],
            'CLP' => ['Chilean Peso',                  '$'],
            'CNY' => ['Chinese Yuan',                  '¥'],
            'COP' => ['Colombian Peso',                '$'],
            'CRC' => ['Costa Rican Colón',             '₡'],
            'CUP' => ['Cuban Peso',                    '$'],
            'CVE' => ['Cape Verdean Escudo',           '$'],
            'CZK' => ['Czech Koruna',                  'Kč'],
            'DJF' => ['Djiboutian Franc',              'Fdj'],
            'DKK' => ['Danish Krone',                  'kr'],
            'DOP' => ['Dominican Peso',                'RD$'],
            'DZD' => ['Algerian Dinar',                'دج'],
            'EGP' => ['Egyptian Pound',                '£'],
            'ETB' => ['Ethiopian Birr',                'Br'],
            'EUR' => ['Euro',                          '€'],
            'FJD' => ['Fijian Dollar',                 'FJ$'],
            'GBP' => ['British Pound',                 '£'],
            'GEL' => ['Georgian Lari',                 '₾'],
            'GHS' => ['Ghanaian Cedi',                 '₵'],
            'GMD' => ['Gambian Dalasi',                'D'],
            'GTQ' => ['Guatemalan Quetzal',            'Q'],
            'GYD' => ['Guyanese Dollar',               'G$'],
            'HKD' => ['Hong Kong Dollar',              'HK$'],
            'HNL' => ['Honduran Lempira',              'L'],
            'HRK' => ['Croatian Kuna',                 'kn'],
            'HTG' => ['Haitian Gourde',                'G'],
            'HUF' => ['Hungarian Forint',              'Ft'],
            'IDR' => ['Indonesian Rupiah',             'Rp'],
            'ILS' => ['Israeli New Shekel',            '₪'],
            'INR' => ['Indian Rupee',                  '₹'],
            'IQD' => ['Iraqi Dinar',                   'ع.د'],
            'IRR' => ['Iranian Rial',                  '﷼'],
            'ISK' => ['Icelandic Króna',               'kr'],
            'JMD' => ['Jamaican Dollar',               'J$'],
            'JOD' => ['Jordanian Dinar',               'JD'],
            'JPY' => ['Japanese Yen',                  '¥'],
            'KES' => ['Kenyan Shilling',               'KSh'],
            'KGS' => ['Kyrgyzstani Som',               'с'],
            'KHR' => ['Cambodian Riel',                '៛'],
            'KRW' => ['South Korean Won',              '₩'],
            'KWD' => ['Kuwaiti Dinar',                 'KD'],
            'KYD' => ['Cayman Islands Dollar',         'CI$'],
            'KZT' => ['Kazakhstani Tenge',             '₸'],
            'LAK' => ['Laotian Kip',                   '₭'],
            'LBP' => ['Lebanese Pound',                'ل.ل'],
            'LKR' => ['Sri Lankan Rupee',              'Rs'],
            'LYD' => ['Libyan Dinar',                  'LD'],
            'MAD' => ['Moroccan Dirham',               'MAD'],
            'MDL' => ['Moldovan Leu',                  'L'],
            'MKD' => ['Macedonian Denar',              'ден'],
            'MMK' => ['Myanmar Kyat',                  'K'],
            'MNT' => ['Mongolian Tögrög',              '₮'],
            'MOP' => ['Macanese Pataca',               'P'],
            'MUR' => ['Mauritian Rupee',               '₨'],
            'MVR' => ['Maldivian Rufiyaa',             'Rf'],
            'MWK' => ['Malawian Kwacha',               'MK'],
            'MXN' => ['Mexican Peso',                  '$'],
            'MYR' => ['Malaysian Ringgit',             'RM'],
            'MZN' => ['Mozambican Metical',            'MT'],
            'NAD' => ['Namibian Dollar',               'N$'],
            'NGN' => ['Nigerian Naira',                '₦'],
            'NIO' => ['Nicaraguan Córdoba',            'C$'],
            'NOK' => ['Norwegian Krone',               'kr'],
            'NPR' => ['Nepalese Rupee',                '₨'],
            'NZD' => ['New Zealand Dollar',            'NZ$'],
            'OMR' => ['Omani Rial',                    'ر.ع.'],
            'PAB' => ['Panamanian Balboa',             'B/.'],
            'PEN' => ['Peruvian Sol',                  'S/.'],
            'PGK' => ['Papua New Guinean Kina',        'K'],
            'PHP' => ['Philippine Peso',               '₱'],
            'PKR' => ['Pakistani Rupee',               '₨'],
            'PLN' => ['Polish Złoty',                  'zł'],
            'PYG' => ['Paraguayan Guaraní',            '₲'],
            'QAR' => ['Qatari Riyal',                  'ر.ق'],
            'RON' => ['Romanian Leu',                  'lei'],
            'RSD' => ['Serbian Dinar',                 'din'],
            'RUB' => ['Russian Ruble',                 '₽'],
            'RWF' => ['Rwandan Franc',                 'RF'],
            'SAR' => ['Saudi Riyal',                   'ر.س'],
            'SBD' => ['Solomon Islands Dollar',        'SI$'],
            'SCR' => ['Seychellois Rupee',             '₨'],
            'SEK' => ['Swedish Krona',                 'kr'],
            'SGD' => ['Singapore Dollar',              'S$'],
            'SRD' => ['Surinamese Dollar',             '$'],
            'SYP' => ['Syrian Pound',                  '£'],
            'THB' => ['Thai Baht',                     '฿'],
            'TJS' => ['Tajikistani Somoni',            'SM'],
            'TMT' => ['Turkmenistan Manat',            'T'],
            'TND' => ['Tunisian Dinar',                'DT'],
            'TOP' => ['Tongan Paʻanga',                'T$'],
            'TRY' => ['Turkish Lira',                  '₺'],
            'TTD' => ['Trinidad & Tobago Dollar',      'TT$'],
            'TWD' => ['New Taiwan Dollar',             'NT$'],
            'TZS' => ['Tanzanian Shilling',            'TSh'],
            'UAH' => ['Ukrainian Hryvnia',             '₴'],
            'UGX' => ['Ugandan Shilling',              'USh'],
            'USD' => ['US Dollar',                     '$'],
            'UYU' => ['Uruguayan Peso',                '$U'],
            'UZS' => ['Uzbekistani Som',               'лв'],
            'VES' => ['Venezuelan Bolívar',            'Bs.S'],
            'VND' => ['Vietnamese Đồng',               '₫'],
            'VUV' => ['Vanuatu Vatu',                  'VT'],
            'WST' => ['Samoan Tālā',                   'T'],
            'XAF' => ['Central African CFA Franc',     'FCFA'],
            'XCD' => ['East Caribbean Dollar',         '$'],
            'XOF' => ['West African CFA Franc',        'CFA'],
            'XPF' => ['CFP Franc',                     'Fr'],
            'YER' => ['Yemeni Rial',                   '﷼'],
            'ZAR' => ['South African Rand',            'R'],
            'ZMW' => ['Zambian Kwacha',                'ZK'],
        ];
    }
}
