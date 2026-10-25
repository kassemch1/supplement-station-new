<?php

namespace App\Exceptions;

use RuntimeException;

// Checkout found the applied coupon no longer usable; the message is the coupon code
class CouponRejected extends RuntimeException
{
}
