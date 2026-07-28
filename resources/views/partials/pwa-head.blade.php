@php
    $pwaName = config('app.name', 'Laravel');
    $pwaThemeColor = '#3d5ee1';
    $pwaStartUrl = route('dashboard');
@endphp

<meta name="theme-color" content="{{ $pwaThemeColor }}">
<meta name="application-name" content="{{ $pwaName }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $pwaName }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no">

<link rel="manifest" href="{{ asset('manifest.json') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('pwa/icons/icon-192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('pwa/icons/icon-512.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('pwa/icons/apple-touch-icon.png') }}">

<script>
    window.__PWA_CONFIG__ = {
        appName: @json($pwaName),
        themeColor: @json($pwaThemeColor),
        serviceWorkerUrl: @json(asset('service-worker.js')),
        offlineUrl: @json(route('offline')),
        startUrl: @json($pwaStartUrl),
    };

    window.__OFFLINE_ENGINE_CONFIG__ = {
        appName: @json($pwaName),
        userId: @json(auth()->check() ? auth()->id() : null),
        departmentId: @json(auth()->check() ? auth()->user()->depID : null),
        databaseName: 'StoreManagementOffline',
        widgetId: 'store-offline-sync-widget',
    };

    window.__PRODUCTS_ROUTE__ = @json(route('products'));
    window.__PRODUCT_EDIT_BASE__ = @json(url('products/products'));
    window.__PRODUCT_DELETE_BASE__ = @json(url('products/products'));

    window.__FUEL_SALES_CONFIG__ = window.__FUEL_SALES_CONFIG__ || {
        createRoute: @json(route('fuel.sales.create')),
        indexRoute: @json(route('fuel.sales.index')),
        creditIndexRoute: @json(route('fuel.sales.credit.index')),
        customerSearchRoute: @json(route('customers.search')),
        customerCreateRoute: @json(route('customer.add')),
        destroyBaseUrl: @json(url('admin/delete')),
        printRouteTemplate: @json(route('fuel.sales.print', ':id'))
    };

    window.__INVENTORY_CONFIG__ = window.__INVENTORY_CONFIG__ || {
        openingInventoryIndexRoute: @json(route('opening_inventory')),
        openingInventoryCreateRoute: @json(route('opening_inventory.add')),
        openingInventoryStoreRoute: @json(route('opening_inventory.store')),
        openingInventoryEditBaseUrl: @json(url('opening_inventory/opening_inventory')),
        openingInventoryDeleteBaseUrl: @json(url('opening_inventory/opening_inventory')),
        badProductsIndexRoute: @json(route('bad_products')),
        badProductsCreateRoute: @json(route('bad_products.add')),
        badProductsStoreRoute: @json(route('bad_products.store')),
        badProductsDeleteBaseUrl: @json(url('bad_products/bad_products')),
        productSearchRoute: @json(route('products.searchProduct')),
        storeRoute: @json(route('store.Inventory'))
    };

    window.__PURCHASE_CONFIG__ = window.__PURCHASE_CONFIG__ || {
        indexRoute: @json(route('purchases')),
        createRoute: @json(route('purchase.add')),
        storeRoute: @json(route('purchases.store')),
        transactionsRoute: @json(route('purchaseTransactions')),
        productSearchRoute: @json(route('products.searchProduct')),
        supplierSearchRoute: @json(route('payable.searchSupplier'))
    };

    window.__SALES_CONFIG__ = window.__SALES_CONFIG__ || {
        indexRoute: @json(route('sales')),
        createRoute: @json(route('sales.add')),
        storeRoute: @json(route('sales.store')),
        transactionsRoute: @json(route('salesTransactions')),
        transactionInvoiceTemplate: @json(route('salesTransactions.invoice', ':id')),
        searchProductRoute: @json(route('sales.searchProduct')),
        searchCustomerRoute: @json(route('sales.searchCustomer')),
        deleteBaseUrl: @json(url('salesTransactions/salesTransactions'))
    };

    window.__QUOTATION_CONFIG__ = window.__QUOTATION_CONFIG__ || {
        indexRoute: @json(route('quotation')),
        createRoute: @json(route('quotationorders.add')),
        storeRoute: @json(route('quotationorders.store')),
        ordersRoute: @json(route('quotationorders')),
        invoiceTemplate: @json(route('quotation.invoice', ':id')),
        searchProductRoute: @json(route('quotationorders.searchProduct')),
        searchCustomerRoute: @json(route('quotationorders.searchCustomer')),
        deleteBaseUrl: @json(url('quotation/quotation'))
    };
</script>
<script defer src="{{ asset('pwa/pwa.js') }}"></script>
<script defer src="{{ asset('offline/config/business-tables.js') }}"></script>
<script defer src="{{ asset('offline/dexie.min.js') }}"></script>
<script defer src="{{ asset('offline/database.js') }}"></script>
<script defer src="{{ asset('offline/queue.js') }}"></script>
<script defer src="{{ asset('offline/repository.js') }}"></script>
<script defer src="{{ asset('offline/repositories/customer-repository.js') }}"></script>
<script defer src="{{ asset('offline/repositories/product-repository.js') }}"></script>
<script defer src="{{ asset('offline/repositories/fuel-sale-repository.js') }}"></script>
<script defer src="{{ asset('offline/repositories/business-repositories.js') }}"></script>
<script defer src="{{ asset('offline/config/sync-config.js') }}"></script>
<script defer src="{{ asset('offline/sync/sync-manager.js') }}"></script>
<script defer src="{{ asset('offline/sync/sync-client.js') }}"></script>
<script defer src="{{ asset('offline/offline-engine.js') }}"></script>
<script defer src="{{ asset('offline/customer-module.js') }}"></script>
<script defer src="{{ asset('offline/product-module.js') }}"></script>
<script defer src="{{ asset('offline/fuel-sales-module.js') }}"></script>
<script defer src="{{ asset('offline/inventory-module.js') }}"></script>
<script defer src="{{ asset('offline/purchase-module.js') }}"></script>
<script defer src="{{ asset('offline/sales-quotation-module.js') }}"></script>
<script defer src="{{ asset('offline/finance-module.js') }}"></script>
