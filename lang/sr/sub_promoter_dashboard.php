<?php

return [
    'page_title' => 'Kontrolna tabla sub-promotera',
    'eyebrow'    => 'Sub-Promoter',
    'main_heading' => 'Moja Kontrolna Tabla',
    'managed_by_prefix' => 'Upravlja:',
    'no_manager_notice' => 'Još uvek niste dodeljeni promoter menadžeru. Zarađivaćete punu proviziju od svoje prodaje.',

    'financials' => [
        'heading'                  => 'Moje Finansije',
        'commission_earned'        => 'Moja Zaradjena Provizija',
        'all_time_label'           => 'Sve vreme',
        'gross_sales'              => 'Moja Bruto Prodaja',
        'gross_sales_subtext'      => 'Od uspešnih porudžbina (completed/sent)',
        'amount_owed'              => 'Šta Dugujem Svom Menadžeru',
        'amount_owed_subtext'      => 'Bruto prodaja - moja provizija - već prebačeno',
        'commission_last_30'       => 'Moja Provizija (Poslednjih 30 Dana)',
        'commission_last_30_subtext' => 'Poslednjih 30 dana',
        'amount_paid'              => 'Već Prebačeno Mom Menadžeru',
        'gross_sales_last_30'      => 'Bruto Prodaja (Poslednjih 30 Dana)',
        'debt_breakdown'           => 'Kako se računa moj dug',
        'debt_formula'             => 'Bruto prodaja − moja provizija − već poslate uplate = dug',
        'debt_payoff_indicator'    => 'Sve izmireno sa menadžerom',
        'debt_overpaid_indicator'  => 'Preplatili ste za',
    ],

    'pyramid' => [
        'heading'           => 'Kako Teču Novac',
        'help'              => 'Svaka ulaznica koju prodate ima tri dela: kupac plaća punu cenu ulaznice, vaš promoter menadžer zadržava svoj deo, a vi zadržavate svoj. Iznos koji dugujete menadžeru je cena ulaznice minus samo vaša provizija.',
        'row_gross'         => 'Bruto prihod od vaših prodaja',
        'row_sub_commission'=> 'Vaša provizija (ovo zadržavate)',
        'row_amount_due'    => 'Iznos koji dugujete menadžeru',
        'row_already_paid'  => 'Već plaćeno menadžeru',
        'row_remaining'     => 'Preostali dug',
    ],

    'performance' => [
        'heading'         => 'Moj Učinak',
        'orders_all_time' => 'Porudžbine (Sve Vreme)',
        'tickets_all_time'=> 'Prodate Ulaznice (Sve Vreme)',
        'orders_last_30'  => 'Porudžbine (Poslednjih 30 Dana)',
        'tickets_last_30' => 'Prodate Ulaznice (Poslednjih 30 Dana)',
    ],

    'top_tickets' => [
        'heading'         => 'Moja Najbolja Prodaja po Tipu Ulaznice',
        'help'            => 'Najprodavaniji tipovi ulaznica iz porudžbina koje ste vi izvršili. Kliknite na broj porudžbine da vidite QR kodove.',
        'no_data'         => 'Još uvek niste prodali nijednu ulaznicu, ili nema dostupnih podataka o završenim prodajama.',
        'header_type'     => 'Tip Ulaznice',
        'header_quantity' => 'Prodata Količina',
        'header_revenue'  => 'Bruto Prihod',
    ],

    'status_breakdown' => [
        'heading' => 'Statusi Mojih Porudžbina',
        'help'    => 'Prikaz svih vaših porudžbina po statusu obrade.',
        'empty'   => 'Još nema porudžbina.',
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
        'empty_title'       => 'Još nema porudžbina',
        'new_order_button'  => 'Nova Porudžbina',
        'view_all_button'   => 'Prikaži sve porudžbine',
        'header_order'      => 'Porudžbina',
        'header_customer'   => 'Kupac',
        'header_total'      => 'Ukupno',
        'header_status'     => 'Status',
    ],

    'record_payment_notice' => [
        'heading'           => 'Uplate',
        'helper_text'       => 'Vi ne evidentirate uplate sami — vaš promoter menadžer beleži svaki prenos za vas.',
        'body'              => 'Stanje iznad odražava sve uplate koje je vaš menadžer evidentirao za vas. Da biste izvršili uplatu, predajte keš svom menadžeru i zamolite ga da to evidentira. Vaš menadžer je jedina osoba koja može uneti uplatu u sistem.',
    ],

    'payment_history' => [
        'heading'           => 'Istorija Mojih Uplata',
        'sub_heading'       => 'Svaka uplata koju je vaš promoter menadžer evidentirao za vas, od najnovije ka starijoj. Vaš menadžer je jedina osoba koja može dodati unose ovde, tako da je ova lista definitivni dnevnik onoga što ste platili.',
        'date'              => 'Datum',
        'amount'            => 'Iznos',
        'direction'         => 'Smer',
        'direction_to'      => 'Ka menadžeru',
        'note'              => 'Napomena',
        'recorded_by'       => 'Evidentirao',
        'empty'             => 'Još nema evidentiranih uplata.',
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
            'header_actions'       => 'Akcije',
            'actions_view_button'  => 'Prikaži / QR kodovi',
            'empty'                => 'Još uvek niste izvršili nijednu porudžbinu.',
        ],
    ],
];
