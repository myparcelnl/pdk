// Define all allowed carrier names as a type
export type CarrierName =
    | 'BOL'
    | 'BPOST'
    | 'CHEAP_CARGO'
    | 'DHL_EUROPLUS'
    | 'DHL_FOR_YOU'
    | 'DHL_PARCEL_CONNECT'
    | 'DPD'
    | 'GLS'
    | 'POSTNL'
    | 'UPS_EXPRESS_SAVER'
    | 'UPS_STANDARD';

type PackageType =
    | 'PACKAGE'
    | 'MAILBOX'
    | 'UNFRANKED'
    | 'DIGITAL_STAMP'
    | 'SMALL_PACKAGE'
    | 'PALLET'
    | 'LETTER';

type DeliveryType =
    | 'EVENING_DELIVERY'
    | 'EXPRESS_DELIVERY'
    | 'MORNING_DELIVERY'
    | 'PICKUP_DELIVERY'
    | 'PICKUP_EXPRESS_DELIVERY'
    | 'SAME_DAY_DELIVERY'
    | 'STANDARD_DELIVERY';

type ShipmentOption =
    | 'AGE_CHECK'
    | 'COLLECT'
    | 'COOLED_DELIVERY'
    | 'DROP_OFF_AT_POSTAL_POINT'
    | 'INSURANCE'
    | 'HIDE_SENDER'
    | 'LARGE_FORMAT'
    | 'ONLY_RECIPIENT'
    | 'RECEIPT_CODE'
    | 'RETURN'
    | 'SAME_DAY_DELIVERY'
    | 'SATURDAY_DELIVERY'
    | 'SIGNATURE'
    | 'TRACKED';
// TODO: refactor and complete this definition, needs to be better typed!
type Metadata =
  | 'labelDescriptionLength'
  | 'carrierSmallPackageContract'
  | 'multiCollo'
  | 'needsCustomerInfo';

type CarrierFeatures = {
  packageTypes: PackageType[];
  deliveryTypes: DeliveryType[];
  shipmentOptions: ShipmentOption[];
  metadata: {
    [K in Metadata]?: boolean | number | string;
  };
};

/**
 * The proposition config is provided by the platform and contains
 * information about the proposition. These details are crucial for loading the application
 * and displaying the proposition correctly.
 *
 * This will tell the application what the application is allowed to do, show, and how to behave.
 */
export type PropositionConfig = {
  // do we need this? it should not affect the application but can be used to load other configs
  proposition: {
    id: number; // this will be converted to a string in a later stage
    key: string;
    name: string;
    shortName: string;
  };
  applicationUrls: {
    backoffice: string;
    support: string;
    consumerPortal: string;
  };
  countryCode: string;
  subscriptions: {
    analytics: {
      availableDashboards: string[];
    };
    shippingRules: {
      countryCodes: string[];
      regionCodes: string[];
      maxAmount: number; // concat of max free shipping rules and max shipping rules (subscription capability)
    };
  };
  internationalization: {
    language: string;
    supportedLanguages: string[];
    dateFormats: Record<string, Record<string, string>>; // nice to have
  };
  consumerPortal: {
    url: string;
    internationalization: {
      language: string;
      supportedLanguages: string[];
      dateFormats: Record<string, string>;
    };
  };
  retailLocations: {
    countries: string[];
  };
  billing: {
    invoiceDates: {
      weekly: string;
      monthly: string;
    };
  };
  contracts: {
    available: {
      id: number;
      carrier: {
        id: number;
        name: string;
      };
      hasPostponedDelivery: boolean;
      inboundFeatures: CarrierFeatures; // do we need this? integrations needs it
      outboundFeatures?: CarrierFeatures; // do we need this? integrations needs it
    }[];
    availableForCustomCredentials: {
      id: number;
      name: string;
      carrier: string;
    }[];
    inbound: {
      default: {id: number; name: string; carrier: {id: number; name: string}};
    } & Record<
      string,
      {id: number; name: string; carrier: {id: number; name: string}}
    >;
    outbound: {
      default: {id: number; name: string; carrier: {id: number; name: string}};
    } & Record<
      string,
      {id: number; name: string; carrier: {id: number; name: string}}
    >;
  };
  paymentProvider: string;
  companyDetails: {
    billing: {
      coc: string;
    };
    contact: {
      phoneNumber: {
        plain: string;
        text: string;
        openingHours: Record<string, {opens: string; closes: string}>;
      };
      whatsapp: {
        url: string;
        openingHours: Record<string, {opens: string; closes: string}>;
      };
    };
    address: {
      street: string;
      postalCode: string;
      // @todo complete address
    };
  };
  centerOfCountry: {
    latitude: number;
    longitude: number;
    zoom: number;
  };
  assets: {
    url: string;
    logo: Record<'medium' | 'small', {url: string}>;
  };
  exampleDocuments: {
    products: {
      csv: string;
    };
    orders: {
      csv: string;
    };
    shipments: {
      csv: string;
    };
  };
  easyReturnServiceCountryCodes: string[];
  allowedReturnCountryCodes: string[];
  rules: {
    country: {
        withRequiredPhoneNumber: string[]; // countryCodes
        withRecommendedPhoneNumber: string[]; // countryCodes
        withRequiredEmail: string[]; // countryCodes
        withAddressFinder: string[]; // countryCodes
    },
    packageType: {
      withRequiredPhoneNumber: PackageType[]; // packageTypes
      withRecommendedPhoneNumber: PackageType[]; // packageTypes
      withRequiredEmail: PackageType[]; // packageTypes
      withAddressFinder: PackageType[]; // packageTypes
    },
  };
  enablePalletSupport: boolean;
  enablePrinterlessReturn: boolean;
  enableLogoOnLabel: boolean;
  enableShipmentEstimate: boolean;
  enableFeedbackModal: boolean;
  enableCompanyDetailsEdit: boolean;
  enableChamberOfCommerceSupport: boolean;
  enableVATSupport: boolean;
  enableMyParcelWebShop: boolean;
  enableImportExport: boolean;
  requiresEmailRecipient: boolean;
  support: {
    flow: 'basic' | 'advanced';
    availableTypes: string[];
    availableSubTypes: Record<string, string[]>;
  };
  bankMandate: {
    flow: 'manual' | 'automatic' | 'none';
    banks: string[];
    showBankNumber: boolean;
  };
  integrations: {
    available: Record<string, unknown>[];
  };
  content: UrlConfig;
  abTesting: {
    script: string;
  };
  // what are these based on? is it the proposition or are more properties needed?
  weightCategories: Record<
    string,
    {
      median: number;
      min: number;
      max: number;
    }[]
  >;
  tools: {
    googleTagManager: {
      id: string;
      url: string;
    };
    appcues: {
      id: string;
    };
  };
};

/**
 * This is a list of urls that can differ per proposition.
 * This is not crucial for the application to run, but it is needed to provide a better user experience.
 */
type UrlConfig = {
  chromeExtensionUrl: string;
  directPrinting: {
    // should be defined for every language
    faqUrls: Record<string, string>;
    printApplicationDownloadUrls: Record<'windows' | 'mac', string>;
  };
  manuals: {
    myContracts: {
      dpd: string;
      dhl: string;
      upsstandard: string;
      upsexpresssaver: string;
      gls: string;
    };
  };
  finance: {
    carrierComparePriceUrl: string;
    priceScalesPolicy: string;
  };
  returns: string;
  support: string;
  integrations: Record<string, string>;
  authenticators: Record<string, {name: string; url: string}>;
  externalIntegrationSupportUrls: {
    amazon: string;
    bol_dot_com: string;
    etsy: string;
    exact: string;
    wix: string;
  };
  myParcelWebShop: string;
  // legal links?
};
