<?php

return [
    'page_title' => 'Kontrolna tabla sub-promotera',
    'main_heading' => 'Moja Kontrolna Tabla',
    'managed_by_prefix' => 'Upravlja:',
    'no_manager_notice' => 'Još uvek niste dodeljeni promoter menadžeru. Zarađivaćete punu proviziju od svoje prodaje.',

    'stats' => [
        'heading'           => 'Moj Performans',
        'commission_earned' => 'Moja Ukupna Zarada',
        'orders'            => 'Moje Porudžbine',
        'tickets_sold'      => 'Moje Prodate Ulaznice',
    ],

    'commission_split' => [
        'heading'           => 'Podela Provizije Koju je Postavio Moj Menadžer',
        'help'              => 'Ovo pokazuje koliko VI zarađujete od svakog tipa ulaznice, prema podešavanju vašeg menadžera. Procenat znači da dobijate X% provizije po nivoima koju vaš menadžer ostvaruje. Fiksni iznos u RSD znači da dobijate tačno taj iznos po ulaznici, bez obzira na nivo.',
        'unknown_type'      => 'Nepoznat tip ulaznice',
        'per_ticket_suffix' => 'po ulaznici',
    ],

    'recent_orders' => [
        'heading'           => 'Moje Nedavne Porudžbine',
        'empty'             => 'Još nema porudžbina.',
        'new_order_button'  => 'Nova Porudžbina',
        'view_all_button'   => 'Prikaži sve porudžbine',
        'header_customer'   => 'Kupac',
        'header_total'      => 'Ukupno',
        'header_status'     => 'Status',
    ],

    'orders' => [
        'page_title'        => 'Moje Porudžbine',
        'main_heading'      => 'Moje Porudžbine',
        'sub_heading'       => 'Svaka porudžbina koju ste izvršili, sa provizijom koju ste VI zaradili na njoj. Porudžbine drugih sub-promotera se ne prikazuju ovde.',
        'back_to_dashboard' => '&larr; Nazad na kontrolnu tablu',
        'new_order_button'  => 'Nova Porudžbina',
        'table' => [
            'header_order'         => 'Porudžbina',
            'header_customer'      => 'Kupac',
            'header_date'          => 'Datum',
            'header_items'         => 'Stavke',
            'header_total'         => 'Ukupno',
            'header_my_commission' => 'Moja Provizija',
            'header_status'        => 'Status',
            'empty'                => 'Još uvek niste izvršili nijednu porudžbinu.',
        ],
    ],
];
