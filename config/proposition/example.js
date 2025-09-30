Event

const event = new CustomEvent("myparcel_update_delivery_options", {
    detail: {
        "address": {
            "cc": "NL",
            "street": "Antareslaan 31",
            "postalCode": "2132 JE",
            "city": "Hoofddorp"
        },
        "config": {
            "platform": "myparcel",
            "packageType": "package",
            "dropOffDays": [
                {
                    "weekday": 2,
                    "cutoffTime": "17:00",
                    "cutoffTimeSameDay": "09:30"
                },
                {
                    "weekday": 3,
                    "cutoffTime": "17:00",
                    "cutoffTimeSameDay": "09:30"
                },
                {
                    "weekday": 4,
                    "cutoffTime": "17:00",
                    "cutoffTimeSameDay": "09:30"
                },
                {
                    "weekday": 5,
                    "cutoffTime": "17:00",
                    "cutoffTimeSameDay": "09:30"
                }
            ],
            "dropOffDelay": 1,
            "deliveryDaysWindow": 7,
            "pickupLocationsDefaultView": "map",
            "showPriceZeroAsFree": false,
            "carrierSettings": {
                "postnl": {
                    "allowDeliveryOptions": true,
                    "allowStandardDelivery": true,
                    "priceStandardDelivery": 5.22,
                    "allowExpressDelivery": true,
                    "priceExpressDelivery": 12.62,
                    "allowMorningDelivery": true,
                    "priceMorningDelivery": 7.09,
                    "allowEveningDelivery": true,
                    "priceEveningDelivery": 7.75,
                    "allowSameDayDelivery": true,
                    "priceSameDayDelivery": 11.65,
                    "allowMondayDelivery": true,
                    "priceMondayDelivery": 6.75,
                    "allowSaturdayDelivery": true,
                    "priceSaturdayDelivery": 6.89,
                    "allowOnlyRecipient": true,
                    "priceOnlyRecipient": 0.73,
                    "allowSignature": true,
                    "priceSignature": 0.73,
                    "pricePackageTypeMailbox": 4.27,
                    "pricePackageTypeDigitalStamp": 3.44,
                    "pricePackageTypePackageSmall": 2.73,
                    "allowPickupLocations": true,
                    "pricePickup": -0.29
                },
            },
            "locale": "nl"
        },
        "platformConfig": {
            "carriers": [
                {
                    "name": "postnl",
                    "subscription": -1,
                    "packageTypes": [
                        "package",
                        "mailbox",
                        "digital_stamp",
                        "package_small"
                    ],
                    "deliveryTypes": [
                        "standard",
                        "morning",
                        "evening",
                        "pickup",
                        "monday"
                    ],
                    "deliveryCountries": [
                        "NL",
                        "BE"
                    ],
                    "pickupCountries": [
                        "NL",
                        "BE",
                        "DK",
                        "SE",
                        "DE"
                    ],
                    "smallPackagePickupCountries": [
                        "NL",
                        "BE"
                    ],
                    "fakeDelivery": true,
                    "shipmentOptionsPerPackageType": {
                        "package": [
                            "only_recipient",
                            "signature"
                        ],
                        "package_small": [
                            "only_recipient",
                            "signature"
                        ]
                    },
                    "features": [
                        "deliveryDaysWindow",
                        "dropOffDays",
                        "dropOffDelay",
                        "pickupMapAllowLoadMore"
                    ],
                    "addressFields": [
                        "postalCode",
                        "street",
                        "city"
                    ]
                }
            ]
        }
    },
});
document.dispatchEvent(event);
