<?php

namespace App\Exceptions;

use RuntimeException;

/** خطأ متوقع أثناء إتمام الطلب (مثل نفاد المخزون) — رسالته آمنة للعرض للزبون. */
class CheckoutException extends RuntimeException {}
