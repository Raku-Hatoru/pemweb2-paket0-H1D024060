<?php

namespace App;

enum BorrowingStatus: string
{
    case Dipinjam = 'dipinjam';
    case Dikembalikan = 'dikembalikan';
    case Terlambat = 'terlambat';
}
