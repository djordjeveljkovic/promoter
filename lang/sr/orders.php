<?php // resources/lang/sr/promoter_orders.php

return [
    'page_title' => 'Moje Porudžbine Ulaznica',
    'main_heading' => 'Moje Izvršene Porudžbine Ulaznica',
    'create_new_order_button' => 'Kreiraj Novu Porudžbinu',

    'table' => [
        'header_order_id' => 'ID Porudžbine',
        'header_seller'   => 'Prodavac',
        'header_customer_email' => 'Email Kupca',
        'header_order_date' => 'Datum Porudžbine',
        'header_items' => 'Stavke',
        'header_total_price' => 'Ukupna Cena',
        'header_commission_earned' => 'Zarađena Provizija',
        'header_job_status' => 'Status Posla',
        'header_actions' => 'Akcije',

        'commission_not_calculated' => 'Nije Obračunato',
        'status_error_tooltip_prefix' => 'Kliknite da vidite detalje greške:',
        'actions_retry_images_button' => 'Ponovi Slike',
        'actions_retry_images_tooltip_prefix' => 'Ponovo generisanje slika/QR kodova. Greška:',
        'actions_retry_email_button' => 'Ponovi Email',
        'actions_retry_email_tooltip_prefix' => 'Ponovno slanje email-a. Greška:',
        'actions_resend_email_button' => 'Ponovo Pošalji Email',
        'actions_resend_email_tooltip' => 'Ponovo pošalji email potvrde.',
        'actions_view_button' => 'Pregled',
        'actions_view_tooltip' => 'Pregledaj detalje porudžbine i karte',
        'job_failure_reason_label' => 'Razlog Neuspeha Posla:',
        'no_orders_message' => "Još uvek niste izvršili nijednu porudžbinu.",
    ],

    'create_page_title' => 'Kreiraj Novu Porudžbinu',
    'create_main_heading' => 'Kreiraj Novu Porudžbinu',
    'create_back_to_orders_link' => 'Nazad na Porudžbine',
    'create_customer_email_label' => 'Email Kupca',

    'create_order_items_heading' => 'Stavke Porudžbine',
    'create_ticket_type_label' => 'Tip Ulaznice',
    'create_select_ticket_type_option' => 'Izaberite tip ulaznice...',
    'create_quantity_label' => 'Količina',
    'create_add_item_button' => 'Dodaj Stavku',

    'create_items_table_header_ticket' => 'Ulaznica',
    'create_items_table_header_quantity' => 'Količina',
    'create_items_table_header_unit_price' => 'Cena po komadu',
    'create_items_table_header_subtotal' => 'Međuzbir',
    'create_items_table_header_remove' => 'Ukloni',
    'create_no_items_message' => 'Nema dodatih stavki.',

    'create_total_label' => 'Ukupno',
    'create_cancel_button' => 'Otkaži',
    'create_submit_button' => 'Izvrši Porudžbinu i Pošalji Ulaznice',

    // Baner sa podelom provizije prikazan sub-promoterima na formi za kreiranje porudžbine
    'commission_split_notice_title'       => 'Podela Provizije',
    'commission_split_notice_managed_by'  => 'Vaš promoter menadžer je :name. Provizija će biti podeljena prema pravilima ispod:',
    'commission_split_notice_default'     => 'Nisu podešena specifična pravila podele. Zadržaćete 100% provizije od svoje prodaje.',
    'commission_split_notice_no_manager'  => 'Niste dodeljeni promoter menadžeru, pa zadržavate 100% provizije.',
    // Translatable job statuses
    'statuses' => [
        'pending' => 'Na čekanju',
        'processing' => 'U obradi',
        'failed' => 'Neuspešno',
        'blocked' => 'Blokirano',
        'completed' => 'Završeno',
        'sent' => 'Poslato',
        'unknown' => 'N/A',
    ],

    'seller_self_badge' => 'Vi',
    'seller_unknown'    => 'Nepoznat prodavac',

    // Banner shown on /promoter/orders for supreme-admin sellers. Explains
    // that the list shows their PRIVATE sales which no one else can see.
    'private_banner'    => 'Privatne prodaje — vidljive samo vama',

    'show_page_title' => 'Porudžbina :orderNumber',
    'show' => [
        'eyebrow'                       => 'Detalji porudžbine',
        'main_heading'                  => 'Porudžbina #:orderNumber',
        'sub_heading'                   => 'Potpuni detalji porudžbine sa provizijom koju ste VI lično zaradili i QR kodovima za svaku prodate ulaznicu.',
        'back_to_orders'                => 'Nazad na porudžbine',
        'summary' => [
            'customer_label'          => 'Kupac',
            'placed_on_label'         => 'Datum',
            'total_label'             => 'Ukupno',
            'status_label'            => 'Status',
            'seller_label'            => 'Prodao',
            'my_commission_label'     => 'Moja provizija na ovoj porudžbini',
            'commission_split_note'   => 'Ukupan fond provizije je :total RSD - ostatak je otišao sub-promoteru prodavca prema podešavanjima.',
        ],
        'items' => [
            'heading'           => 'Stavke',
            'header_type'       => 'Tip ulaznice',
            'header_quantity'   => 'Kol.',
            'header_unit_price' => 'Cena po komadu',
            'header_subtotal'   => 'Međuzbir',
            'unknown_type'      => 'Nepoznat tip',
        ],
        'tickets' => [
            'heading'              => 'Ulaznice i QR kodovi',
            'sub_heading'          => ':count ulaznica generisano za ovu porudžbinu. Kliknite na QR sliku da je otvorite u punoj veličini.',
            'empty'                => 'Još uvek nema generisanih ulaznica za ovu porudžbinu.',
            'image_alt_prefix'     => 'QR kod za ulaznicu',
            'card_title_prefix'    => 'Ulaznica #',
            'unknown_type'         => 'Nepoznat tip',
            'status_active'        => 'Aktivna',
            'status_inactive'      => 'Neaktivna',
            'qr_not_available'     => 'QR kod još nije generisan',
            'download_all_button'  => 'Preuzmi sve QR kodove',
            'restricted_notice_title'   => 'QR kodovi su ograničeni',
            'restricted_notice_body'    => 'QR kodovi za ovu porudžbinu nisu vidljivi za vašu ulogu (sub-promoter / promoter menadžer).',
        ],
    ],
];
