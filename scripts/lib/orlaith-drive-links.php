<?php

/**
 * Google Drive folder + Word draft URLs for Orlaith's August pages.
 *
 * @return array<string, array{drive_folder: string, drive_word: string}>
 */
function matrix_orlaith_drive_links(): array
{
    $folder = static fn (string $id): string => 'https://drive.google.com/drive/folders/' . $id;
    $file = static fn (string $id): string => 'https://drive.google.com/file/d/' . $id . '/view';

    return [
        'PAGE-114' => [
            'drive_folder' => $folder('17CN2WbqtqxTdyeza4HvAEVBs_rOlZUkd'),
            'drive_word' => $file('1zAdcA_pInqPfnlRnHFoT-mPDfg873YeG'),
        ],
        'PAGE-124' => [
            'drive_folder' => $folder('1ZZ-5zruR6VrSZJX9WLyE0QkF1Bl7oNFV'),
            'drive_word' => $file('1z0VqJePD33LvhlpJKUhcj-TyIxb9q911'),
        ],
        'PAGE-128' => [
            'drive_folder' => $folder('177ttrS8riFYoln8BXIa2C1fXEhhlN7gi'),
            'drive_word' => '',
        ],
        'PAGE-145' => [
            'drive_folder' => $folder('1vVBI4nJptYCPMsL4wnjjBpX9u3mNfWHo'),
            'drive_word' => $file('1xNuIU1L7PYLno0wi7gk3rn0jlb53YscY'),
        ],
        'PAGE-146' => [
            'drive_folder' => $folder('12Rr96dgmukyWGcnukC9v7Ix53asz0V_d'),
            'drive_word' => $file('1qi8XKGq0Sq4m1AlHIRQ-vQYDBpsVvhol'),
        ],
        'PAGE-147' => [
            'drive_folder' => $folder('1y_scgOP_2Zv5bhIcG9oom2NZJMSeiJ3v'),
            'drive_word' => $file('19BpBRM6vgPwDDUdN8gdUp9Od5djjKlYA'),
        ],
        'PAGE-148' => [
            'drive_folder' => $folder('1TOLB6ROrT_yKkZKVl1jXjEy1Z_rf66Yg'),
            'drive_word' => $file('1FFqyvpbkncewq7--fqGcsmimeEpJFXRe'),
        ],
        'PAGE-149' => [
            'drive_folder' => $folder('18iKYkRfsY2tmclXEKkfjyyh6qYLxToOV'),
            'drive_word' => $file('1ZaQb-xxHCKk8UssXNIof2y6q0j-5RzYB'),
        ],
        'PAGE-150' => [
            'drive_folder' => $folder('18_ZkllXwBEJbWQdxxCAO_cH0iPJh2HHt'),
            'drive_word' => $file('1luUFuZDMu03BIoEeO0jlD_VjqhBlfnuZ'),
        ],
        'PAGE-151' => [
            'drive_folder' => $folder('1rJ1D-RkrKHM8y1wk2z6JuEzqcgVAA9zQ'),
            'drive_word' => $file('1x3sjj2NF9Z9ISQ9M0CewPf7W6g9YeoBi'),
        ],
        'PAGE-152' => [
            'drive_folder' => $folder('1CnvvV_EIY9mwt5n3G8SdvB6dqR2IRfQP'),
            'drive_word' => $file('1jzEiC54UhPFI262SV_Iuu96_oFwgx6p8'),
        ],
        'PAGE-153' => [
            'drive_folder' => $folder('1Xk3YPQRfDgvranwvZeaLVF42eMfjROFz'),
            'drive_word' => $file('11wojmIvmHM_bD976IScyGgBuBER4xTWa'),
        ],
        'PAGE-154' => [
            'drive_folder' => $folder('1gJnE6hQ1r24VOQfqQRppTNpp12jG5tK5'),
            'drive_word' => $file('1PaG1k5m3rP_KFmVCefdvFGP_w3CEaFVu'),
        ],
        'PAGE-155' => [
            'drive_folder' => $folder('1XFyBovYDZIOLzgeXgPMlsxp1XwDP35NF'),
            'drive_word' => $file('1D09mUoS4KeqzXo8KMpNbr-0whn3OHmL1'),
        ],
        'PAGE-156' => [
            'drive_folder' => $folder('1G3nKX6Z8dNvrpoIhYdWDKAdaqOVWSw45'),
            'drive_word' => $file('12A8xafPghWhvFbPPoVEsaz9lce8tgpCj'),
        ],
    ];
}
