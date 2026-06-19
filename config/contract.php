<?php

return [

    // Operator (甲) info used to fill in contract signature sections via {operator_company} / {operator_name} / {operator_address}
    'operator_company' => env('OPERATOR_COMPANY'),
    'operator_name'    => env('OPERATOR_NAME'),
    'operator_address' => env('OPERATOR_ADDRESS'),

    // Bank account info displayed on invoice PDFs
    'bank_name'           => env('BANK_NAME'),
    'bank_branch'         => env('BANK_BRANCH'),
    'bank_account_type'   => env('BANK_ACCOUNT_TYPE', '普通'),
    'bank_account_number' => env('BANK_ACCOUNT_NUMBER'),
    'bank_account_name'   => env('BANK_ACCOUNT_NAME'),

];
