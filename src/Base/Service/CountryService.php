<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

class CountryService
{
    /**
     * Country codes.
     */
    public const  CC_MA            = 'MA';
    public const  CC_MC            = 'MC';
    public const  CC_MD            = 'MD';
    public const  CC_ME            = 'ME';
    public const  CC_MF            = 'MF';
    public const  CC_MG            = 'MG';
    public const  CC_MH            = 'MH';
    public const  CC_MK            = 'MK';
    public const  CC_ML            = 'ML';
    public const  CC_MM            = 'MM';
    public const  CC_LR            = 'LR';
    public const  CC_LS            = 'LS';
    public const  CC_LT            = 'LT';
    public const  CC_LU            = 'LU';
    public const  CC_LV            = 'LV';
    public const  CC_LY            = 'LY';
    public const  CC_NA            = 'NA';
    public const  CC_NC            = 'NC';
    public const  CC_NE            = 'NE';
    public const  CC_NF            = 'NF';
    public const  CC_NG            = 'NG';
    public const  CC_NI            = 'NI';
    public const  CC_NL            = 'NL';
    public const  CC_MN            = 'MN';
    public const  CC_MO            = 'MO';
    public const  CC_MP            = 'MP';
    public const  CC_MQ            = 'MQ';
    public const  CC_MR            = 'MR';
    public const  CC_MS            = 'MS';
    public const  CC_MT            = 'MT';
    public const  CC_MU            = 'MU';
    public const  CC_MV            = 'MV';
    public const  CC_MW            = 'MW';
    public const  CC_MX            = 'MX';
    public const  CC_MY            = 'MY';
    public const  CC_MZ            = 'MZ';
    public const  CC_KE            = 'KE';
    public const  CC_KG            = 'KG';
    public const  CC_KH            = 'KH';
    public const  CC_KI            = 'KI';
    public const  CC_JM            = 'JM';
    public const  CC_JO            = 'JO';
    public const  CC_JP            = 'JP';
    public const  CC_LA            = 'LA';
    public const  CC_LB            = 'LB';
    public const  CC_LC            = 'LC';
    public const  CC_LI            = 'LI';
    public const  CC_LK            = 'LK';
    public const  CC_KM            = 'KM';
    public const  CC_KN            = 'KN';
    public const  CC_KP            = 'KP';
    public const  CC_KR            = 'KR';
    public const  CC_KW            = 'KW';
    public const  CC_KY            = 'KY';
    public const  CC_KZ            = 'KZ';
    public const  CC_IC            = 'IC';
    public const  CC_ID            = 'ID';
    public const  CC_YT            = 'YT';
    public const  CC_IE            = 'IE';
    public const  CC_HK            = 'HK';
    public const  CC_HM            = 'HM';
    public const  CC_HN            = 'HN';
    public const  CC_HR            = 'HR';
    public const  CC_HT            = 'HT';
    public const  CC_YE            = 'YE';
    public const  CC_HU            = 'HU';
    public const  CC_ZM            = 'ZM';
    public const  CC_JE            = 'JE';
    public const  CC_ZW            = 'ZW';
    public const  CC_IL            = 'IL';
    public const  CC_IM            = 'IM';
    public const  CC_IN            = 'IN';
    public const  CC_IO            = 'IO';
    public const  CC_ZA            = 'ZA';
    public const  CC_IQ            = 'IQ';
    public const  CC_IR            = 'IR';
    public const  CC_IS            = 'IS';
    public const  CC_IT            = 'IT';
    public const  CC_GA            = 'GA';
    public const  CC_GB            = 'GB';
    public const  CC_WS            = 'WS';
    public const  CC_GD            = 'GD';
    public const  CC_GE            = 'GE';
    public const  CC_GF            = 'GF';
    public const  CC_GG            = 'GG';
    public const  CC_FI            = 'FI';
    public const  CC_FJ            = 'FJ';
    public const  CC_FK            = 'FK';
    public const  CC_FM            = 'FM';
    public const  CC_FO            = 'FO';
    public const  CC_FR            = 'FR';
    public const  CC_WF            = 'WF';
    public const  CC_GY            = 'GY';
    public const  CC_XK            = 'XK';
    public const  CC_XL            = 'XL';
    public const  CC_XM            = 'XM';
    public const  CC_GH            = 'GH';
    public const  CC_GI            = 'GI';
    public const  CC_GL            = 'GL';
    public const  CC_GM            = 'GM';
    public const  CC_GN            = 'GN';
    public const  CC_GP            = 'GP';
    public const  CC_GQ            = 'GQ';
    public const  CC_GR            = 'GR';
    public const  CC_XC            = 'XC';
    public const  CC_GS            = 'GS';
    public const  CC_GT            = 'GT';
    public const  CC_GU            = 'GU';
    public const  CC_GW            = 'GW';
    public const  CC_UG            = 'UG';
    public const  CC_DZ            = 'DZ';
    public const  CC_UM            = 'UM';
    public const  CC_EA            = 'EA';
    public const  CC_EC            = 'EC';
    public const  CC_US            = 'US';
    public const  CC_EE            = 'EE';
    public const  CC_DE            = 'DE';
    public const  CC_TV            = 'TV';
    public const  CC_DG            = 'DG';
    public const  CC_TW            = 'TW';
    public const  CC_DJ            = 'DJ';
    public const  CC_TZ            = 'TZ';
    public const  CC_DK            = 'DK';
    public const  CC_DM            = 'DM';
    public const  CC_DO            = 'DO';
    public const  CC_UA            = 'UA';
    public const  CC_VG            = 'VG';
    public const  CC_VI            = 'VI';
    public const  CC_EZ            = 'EZ';
    public const  CC_VN            = 'VN';
    public const  CC_VU            = 'VU';
    public const  CC_EG            = 'EG';
    public const  CC_EH            = 'EH';
    public const  CC_UY            = 'UY';
    public const  CC_UZ            = 'UZ';
    public const  CC_VA            = 'VA';
    public const  CC_ER            = 'ER';
    public const  CC_VC            = 'VC';
    public const  CC_ES            = 'ES';
    public const  CC_ET            = 'ET';
    public const  CC_VE            = 'VE';
    public const  CC_BS            = 'BS';
    public const  CC_SD            = 'SD';
    public const  CC_BT            = 'BT';
    public const  CC_SE            = 'SE';
    public const  CC_BV            = 'BV';
    public const  CC_SG            = 'SG';
    public const  CC_BW            = 'BW';
    public const  CC_SH            = 'SH';
    public const  CC_SI            = 'SI';
    public const  CC_BY            = 'BY';
    public const  CC_SJ            = 'SJ';
    public const  CC_BZ            = 'BZ';
    public const  CC_SK            = 'SK';
    public const  CC_SL            = 'SL';
    public const  CC_SM            = 'SM';
    public const  CC_SN            = 'SN';
    public const  CC_SO            = 'SO';
    public const  CC_CA            = 'CA';
    public const  CC_SR            = 'SR';
    public const  CC_CC            = 'CC';
    public const  CC_SS            = 'SS';
    public const  CC_RS            = 'RS';
    public const  CC_BD            = 'BD';
    public const  CC_BE            = 'BE';
    public const  CC_RU            = 'RU';
    public const  CC_BF            = 'BF';
    public const  CC_BG            = 'BG';
    public const  CC_RW            = 'RW';
    public const  CC_BH            = 'BH';
    public const  CC_BI            = 'BI';
    public const  CC_BJ            = 'BJ';
    public const  CC_BL            = 'BL';
    public const  CC_BM            = 'BM';
    public const  CC_BN            = 'BN';
    public const  CC_BO            = 'BO';
    public const  CC_SA            = 'SA';
    public const  CC_BQ            = 'BQ';
    public const  CC_SB            = 'SB';
    public const  CC_BR            = 'BR';
    public const  CC_SC            = 'SC';
    public const  CC_CU            = 'CU';
    public const  CC_TF            = 'TF';
    public const  CC_CV            = 'CV';
    public const  CC_TG            = 'TG';
    public const  CC_CW            = 'CW';
    public const  CC_TH            = 'TH';
    public const  CC_CX            = 'CX';
    public const  CC_CY            = 'CY';
    public const  CC_TJ            = 'TJ';
    public const  CC_CZ            = 'CZ';
    public const  CC_TK            = 'TK';
    public const  CC_TL            = 'TL';
    public const  CC_TM            = 'TM';
    public const  CC_TN            = 'TN';
    public const  CC_TO            = 'TO';
    public const  CC_TR            = 'TR';
    public const  CC_TT            = 'TT';
    public const  CC_CD            = 'CD';
    public const  CC_ST            = 'ST';
    public const  CC_CF            = 'CF';
    public const  CC_SV            = 'SV';
    public const  CC_CG            = 'CG';
    public const  CC_CH            = 'CH';
    public const  CC_SX            = 'SX';
    public const  CC_CI            = 'CI';
    public const  CC_SY            = 'SY';
    public const  CC_SZ            = 'SZ';
    public const  CC_CK            = 'CK';
    public const  CC_CL            = 'CL';
    public const  CC_CM            = 'CM';
    public const  CC_CN            = 'CN';
    public const  CC_CO            = 'CO';
    public const  CC_TA            = 'TA';
    public const  CC_CR            = 'CR';
    public const  CC_TC            = 'TC';
    public const  CC_TD            = 'TD';
    public const  CC_PR            = 'PR';
    public const  CC_PS            = 'PS';
    public const  CC_PT            = 'PT';
    public const  CC_PW            = 'PW';
    public const  CC_PY            = 'PY';
    public const  CC_QA            = 'QA';
    public const  CC_AR            = 'AR';
    public const  CC_AS            = 'AS';
    public const  CC_AT            = 'AT';
    public const  CC_RE            = 'RE';
    public const  CC_AU            = 'AU';
    public const  CC_AW            = 'AW';
    public const  CC_AX            = 'AX';
    public const  CC_AZ            = 'AZ';
    public const  CC_RO            = 'RO';
    public const  CC_BA            = 'BA';
    public const  CC_BB            = 'BB';
    public const  CC_AC            = 'AC';
    public const  CC_AD            = 'AD';
    public const  CC_AE            = 'AE';
    public const  CC_AF            = 'AF';
    public const  CC_AG            = 'AG';
    public const  CC_AI            = 'AI';
    public const  CC_AL            = 'AL';
    public const  CC_AM            = 'AM';
    public const  CC_AN            = 'AN';
    public const  CC_AO            = 'AO';
    public const  CC_AQ            = 'AQ';
    public const  CC_OM            = 'OM';
    public const  CC_NO            = 'NO';
    public const  CC_NP            = 'NP';
    public const  CC_NR            = 'NR';
    public const  CC_NU            = 'NU';
    public const  CC_NZ            = 'NZ';
    public const  CC_PA            = 'PA';
    public const  CC_PE            = 'PE';
    public const  CC_PF            = 'PF';
    public const  CC_PG            = 'PG';
    public const  CC_PH            = 'PH';
    public const  CC_PK            = 'PK';
    public const  CC_PL            = 'PL';
    public const  CC_PM            = 'PM';
    public const  CC_PN            = 'PN';
    public const  ZONE_EU          = 'EU';
    public const  ZONE_ROW         = 'ROW';
    public const  EU_COUNTRIES     = [
        self::CC_NL,
        self::CC_BE,
        self::CC_AT,
        self::CC_BG,
        self::CC_CZ,
        self::CC_CY,
        self::CC_DK,
        self::CC_EE,
        self::CC_FI,
        self::CC_FR,
        self::CC_DE,
        self::CC_GR,
        self::CC_HU,
        self::CC_IE,
        self::CC_IT,
        self::CC_LV,
        self::CC_LT,
        self::CC_LU,
        self::CC_PL,
        self::CC_PT,
        self::CC_RO,
        self::CC_SK,
        self::CC_SI,
        self::CC_ES,
        self::CC_SE,
        self::CC_XK,
    ];
    public const  ALL              = [
        self::CC_AC,
        self::CC_AD,
        self::CC_AE,
        self::CC_AF,
        self::CC_AG,
        self::CC_AI,
        self::CC_AL,
        self::CC_AM,
        self::CC_AN,
        self::CC_AO,
        self::CC_AQ,
        self::CC_AR,
        self::CC_AS,
        self::CC_AT,
        self::CC_AU,
        self::CC_AW,
        self::CC_AX,
        self::CC_AZ,
        self::CC_BA,
        self::CC_BB,
        self::CC_BD,
        self::CC_BE,
        self::CC_BF,
        self::CC_BG,
        self::CC_BH,
        self::CC_BI,
        self::CC_BJ,
        self::CC_BL,
        self::CC_BM,
        self::CC_BN,
        self::CC_BO,
        self::CC_BQ,
        self::CC_BR,
        self::CC_BS,
        self::CC_BT,
        self::CC_BV,
        self::CC_BW,
        self::CC_BY,
        self::CC_BZ,
        self::CC_CA,
        self::CC_CC,
        self::CC_CD,
        self::CC_CF,
        self::CC_CG,
        self::CC_CH,
        self::CC_CI,
        self::CC_CK,
        self::CC_CL,
        self::CC_CM,
        self::CC_CN,
        self::CC_CO,
        self::CC_CR,
        self::CC_CU,
        self::CC_CV,
        self::CC_CW,
        self::CC_CX,
        self::CC_CY,
        self::CC_CZ,
        self::CC_DE,
        self::CC_DG,
        self::CC_DJ,
        self::CC_DK,
        self::CC_DM,
        self::CC_DO,
        self::CC_DZ,
        self::CC_EA,
        self::CC_EC,
        self::CC_EE,
        self::CC_EG,
        self::CC_EH,
        self::CC_ER,
        self::CC_ES,
        self::CC_ET,
        self::CC_EZ,
        self::CC_FI,
        self::CC_FJ,
        self::CC_FK,
        self::CC_FM,
        self::CC_FO,
        self::CC_FR,
        self::CC_GA,
        self::CC_GB,
        self::CC_GD,
        self::CC_GE,
        self::CC_GF,
        self::CC_GG,
        self::CC_GH,
        self::CC_GI,
        self::CC_GL,
        self::CC_GM,
        self::CC_GN,
        self::CC_GP,
        self::CC_GQ,
        self::CC_GR,
        self::CC_GS,
        self::CC_GT,
        self::CC_GU,
        self::CC_GW,
        self::CC_GY,
        self::CC_HK,
        self::CC_HM,
        self::CC_HN,
        self::CC_HR,
        self::CC_HT,
        self::CC_HU,
        self::CC_IC,
        self::CC_ID,
        self::CC_IE,
        self::CC_IL,
        self::CC_IM,
        self::CC_IN,
        self::CC_IO,
        self::CC_IQ,
        self::CC_IR,
        self::CC_IS,
        self::CC_IT,
        self::CC_JE,
        self::CC_JM,
        self::CC_JO,
        self::CC_JP,
        self::CC_KE,
        self::CC_KG,
        self::CC_KH,
        self::CC_KI,
        self::CC_KM,
        self::CC_KN,
        self::CC_KP,
        self::CC_KR,
        self::CC_KW,
        self::CC_KY,
        self::CC_KZ,
        self::CC_LA,
        self::CC_LB,
        self::CC_LC,
        self::CC_LI,
        self::CC_LK,
        self::CC_LR,
        self::CC_LS,
        self::CC_LT,
        self::CC_LU,
        self::CC_LV,
        self::CC_LY,
        self::CC_MA,
        self::CC_MC,
        self::CC_MD,
        self::CC_ME,
        self::CC_MF,
        self::CC_MG,
        self::CC_MH,
        self::CC_MK,
        self::CC_ML,
        self::CC_MM,
        self::CC_MN,
        self::CC_MO,
        self::CC_MP,
        self::CC_MQ,
        self::CC_MR,
        self::CC_MS,
        self::CC_MT,
        self::CC_MU,
        self::CC_MV,
        self::CC_MW,
        self::CC_MX,
        self::CC_MY,
        self::CC_MZ,
        self::CC_NA,
        self::CC_NC,
        self::CC_NE,
        self::CC_NF,
        self::CC_NG,
        self::CC_NI,
        self::CC_NL,
        self::CC_NO,
        self::CC_NP,
        self::CC_NR,
        self::CC_NU,
        self::CC_NZ,
        self::CC_OM,
        self::CC_PA,
        self::CC_PE,
        self::CC_PF,
        self::CC_PG,
        self::CC_PH,
        self::CC_PK,
        self::CC_PL,
        self::CC_PM,
        self::CC_PN,
        self::CC_PR,
        self::CC_PS,
        self::CC_PT,
        self::CC_PW,
        self::CC_PY,
        self::CC_QA,
        self::CC_RE,
        self::CC_RO,
        self::CC_RS,
        self::CC_RU,
        self::CC_RW,
        self::CC_SA,
        self::CC_SB,
        self::CC_SC,
        self::CC_SD,
        self::CC_SE,
        self::CC_SG,
        self::CC_SH,
        self::CC_SI,
        self::CC_SJ,
        self::CC_SK,
        self::CC_SL,
        self::CC_SM,
        self::CC_SN,
        self::CC_SO,
        self::CC_SR,
        self::CC_SS,
        self::CC_ST,
        self::CC_SV,
        self::CC_SX,
        self::CC_SY,
        self::CC_SZ,
        self::CC_TA,
        self::CC_TC,
        self::CC_TD,
        self::CC_TF,
        self::CC_TG,
        self::CC_TH,
        self::CC_TJ,
        self::CC_TK,
        self::CC_TL,
        self::CC_TM,
        self::CC_TN,
        self::CC_TO,
        self::CC_TR,
        self::CC_TT,
        self::CC_TV,
        self::CC_TW,
        self::CC_TZ,
        self::CC_UA,
        self::CC_UG,
        self::CC_UM,
        self::CC_US,
        self::CC_UY,
        self::CC_UZ,
        self::CC_VA,
        self::CC_VC,
        self::CC_VE,
        self::CC_VG,
        self::CC_VI,
        self::CC_VN,
        self::CC_VU,
        self::CC_WF,
        self::CC_WS,
        self::CC_XC,
        self::CC_XK,
        self::CC_XL,
        self::CC_XM,
        self::CC_YE,
        self::CC_YT,
        self::CC_ZA,
        self::CC_ZM,
        self::CC_ZW,
    ];
    private const UNIQUE_COUNTRIES = [self::CC_NL, self::CC_BE];

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        return self::ALL;
    }

    /**
     * @param  string $country
     *
     * @return string
     */
    public function getShippingZone(string $country): string
    {
        if (in_array($country, self::UNIQUE_COUNTRIES, true)) {
            return $country;
        }

        if (in_array($country, self::EU_COUNTRIES, true)) {
            return self::ZONE_EU;
        }

        return self::ZONE_ROW;
    }

    /**
     * @param  string $country
     *
     * @return bool
     */
    public function isEu(string $country): bool
    {
        return self::ZONE_EU === $this->getShippingZone($country);
    }

    /**
     * @param  string $country
     *
     * @return bool
     */
    public function isRow(string $country): bool
    {
        return self::ZONE_ROW === $this->getShippingZone($country);
    }

    /**
     * @param  string $country
     *
     * @return bool
     */
    public function isUnique(string $country): bool
    {
        return $country === $this->getShippingZone($country);
    }
}
