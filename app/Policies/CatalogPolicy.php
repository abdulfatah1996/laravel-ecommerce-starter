<?php

namespace App\Policies;

/** المنتجات والتصنيفات: الموظف يعرض ويعدّل (مثل المخزون)، المدير ينشئ ويحذف. */
class CatalogPolicy extends RolePolicy {}
