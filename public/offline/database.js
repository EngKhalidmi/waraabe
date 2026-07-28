(function (global) {
    'use strict';

    var DATABASE_NAME = 'StoreManagementOffline';
    var MIGRATIONS = [];
    var BUSINESS_STORE_DEFINITIONS = {};
    var BUSINESS_TABLE_CONFIG = global.StoreManagementBusinessTableConfig || {};
    var databaseInstance = null;
    var readyPromise = null;
    var BASE_BUSINESS_SCHEMA_FIELDS = [
        'server_id',
        'synced',
        'sync_status',
        'local_action',
        'sync_error',
        'last_error',
        'queue_state',
        'created_at',
        'updated_at',
        'is_deleted',
        'deleted_at',
        'depID',
        'customerID',
        'customer_id',
        'salesman_id',
        'product_id',
        'proID',
        'fuel_sale_id',
        'transID',
        'customer_name',
        'full_name',
        'name',
        'supplier',
        'received_from',
        'account',
        'phone',
        'email',
        'address',
        'serial',
        'barcode',
        'sku_code',
        'sku',
        'code',
        'key',
        'type',
        'status',
        'amount',
        'balance',
        'quantity',
        'rate',
        'total',
        'date',
        'description',
        'note',
        'payment_method',
        'reference',
        'invoice_no',
        'invoice_number',
        'transaction_no',
        'ticket_number',
        'shift',
        'net_total',
        'cash_on_hand',
        'pbalance',
        'current',
        'discount',
        'paid_amount',
        'sub_total',
        'net_price',
        'paid_date',
        'check_no',
        'owner_name',
        'capital_amount',
        'opening_quantity',
        'opening_date',
        'reported_date',
        'return_date',
        'payment_account',
        'user',
        'seller',
        'value',
        'info',
        'reason',
        'shift',
        'dphase',
        'liters',
        'previous_reading',
        'current_reading',
        'payment_rate',
        'zaad_dollar',
        'zaad_slsh',
        'edahab_dollar',
        'edahab_slsh',
        'cash_dollar',
        'cash_slsh',
        'merchant_dollar',
        'merchant_slsh',
        'add_cost',
        'unit',
        'price',
        'total_cost',
        'unit_cost',
        'selling_price',
        'actual_price',
        'quantity',
        'age',
        'birthDate',
        'sex'
    ];

    function nowIso() {
        return new Date().toISOString();
    }

    function generateUuid() {
        if (global.crypto && typeof global.crypto.randomUUID === 'function') {
            return global.crypto.randomUUID();
        }

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (character) {
            var random = Math.random() * 16 | 0;
            var value = character === 'x' ? random : (random & 0x3 | 0x8);

            return value.toString(16);
        });
    }

    function isPlainObject(value) {
        return Object.prototype.toString.call(value) === '[object Object]';
    }

    function sortObject(value) {
        if (Array.isArray(value)) {
            return value.map(sortObject);
        }

        if (!isPlainObject(value)) {
            return value;
        }

        return Object.keys(value).sort().reduce(function (accumulator, key) {
            accumulator[key] = sortObject(value[key]);
            return accumulator;
        }, {});
    }

    function stableStringify(value) {
        try {
            return JSON.stringify(sortObject(value));
        } catch (error) {
            return JSON.stringify(String(value));
        }
    }

    function normalizeUrl(url) {
        if (!url) {
            return '';
        }

        try {
            var parsed = new URL(String(url), global.location.origin);
            return parsed.pathname + parsed.search;
        } catch (error) {
            return String(url).trim();
        }
    }

    function formDataToObject(formData) {
        if (formData instanceof FormData) {
            var result = {};

            formData.forEach(function (value, key) {
                var normalizedValue = value;

                if (value instanceof File) {
                    normalizedValue = {
                        __type: 'file',
                        name: value.name,
                        size: value.size,
                        type: value.type,
                        lastModified: value.lastModified
                    };
                }

                if (Object.prototype.hasOwnProperty.call(result, key)) {
                    if (!Array.isArray(result[key])) {
                        result[key] = [result[key]];
                    }

                    result[key].push(normalizedValue);
                    return;
                }

                result[key] = normalizedValue;
            });

            return result;
        }

        if (isPlainObject(formData)) {
            return sortObject(formData);
        }

        return {};
    }

    function normalizeMethod(method) {
        return String(method || 'POST').toUpperCase();
    }

    function buildRequestKey(request) {
        var payload = {
            method: normalizeMethod(request.method),
            url: normalizeUrl(request.url),
            userId: request.userId || null,
            tableName: String(request.table_name || request.tableName || request.store || request.formName || '').trim(),
            recordLocalId: request.record_local_id || request.recordLocalId || null,
            operation: String(request.operation || request.action || '').trim().toLowerCase(),
            payload: formDataToObject(request.payload || request.formData || request.data || {})
        };

        return stableStringify(payload);
    }

    function getBusinessTableConfig() {
        return global.StoreManagementBusinessTableConfig || BUSINESS_TABLE_CONFIG || {};
    }

    function toArray(value) {
        if (Array.isArray(value)) {
            return value.slice();
        }

        if (value === null || typeof value === 'undefined' || value === '') {
            return [];
        }

        return [value];
    }

    function dedupeFields(values) {
        var seen = {};

        return values.filter(function (value) {
            var field = String(value || '').trim();

            if (!field) {
                return false;
            }

            if (seen[field]) {
                return false;
            }

            seen[field] = true;
            return true;
        });
    }

    function buildBusinessStoreSchema(definition) {
        var definitionConfig = definition || {};
        var fields = [];
        var reserved = {
            id: true,
            local_id: true
        };

        fields = fields.concat(BASE_BUSINESS_SCHEMA_FIELDS);
        fields = fields.concat(toArray(definitionConfig.primaryFields));
        fields = fields.concat(toArray(definitionConfig.searchFields));
        fields = fields.concat(toArray(definitionConfig.referenceFields));
        fields = fields.concat(toArray(definitionConfig.indexes));
        fields = fields.concat(toArray(definitionConfig.schemaFields));

        fields = dedupeFields(fields).filter(function (field) {
            return !reserved[field];
        });

        return '++id,&local_id,' + fields.join(',');
    }

    function registerConfiguredBusinessStoreDefinitions() {
        var config = getBusinessTableConfig();

        Object.keys(config).forEach(function (storeName) {
            registerBusinessStoreDefinition(storeName, config[storeName]);
        });
    }

    function buildBusinessMigrationStores() {
        var stores = {
            pending_requests: '++id,&uuid,&request_key,url,method,user_id,sync_status,timestamp,created_at,updated_at,retry_count',
            sync_queue: '++id,&uuid,&request_uuid,&request_key,status,user_id,created_at,updated_at,retry_count',
            settings: '&key,updated_at'
        };

        getBusinessStoreNames().forEach(function (storeName) {
            stores[storeName] = buildBusinessStoreSchema(getBusinessStoreDefinition(storeName));
        });

        return stores;
    }

    function buildSyncMigrationStores() {
        var stores = buildBusinessMigrationStores();

        stores.pending_requests = '++id,&uuid,&request_key,&request_uuid,url,method,table_name,record_local_id,operation,user_id,sync_status,sync_error,last_error,timestamp,created_at,updated_at,retry_count';
        stores.sync_queue = '++id,&uuid,&request_uuid,&request_key,status,table_name,record_local_id,operation,user_id,sync_status,sync_error,last_error,created_at,updated_at,retry_count';
        stores.settings = '&key,updated_at';

        return stores;
    }

    function normalizeRequest(request) {
        var source = Object.assign({}, request || {});
        var payload = formDataToObject(source.payload || source.formData || source.data || {});
        var timestamp = source.timestamp || source.created_at || source.createdAt || nowIso();
        var syncStatus = source.sync_status || source.syncStatus || 'pending';
        var tableName = String(source.table_name || source.tableName || source.store || source.formName || (source.extra && source.extra.storeName) || '').trim();
        var operation = String(source.operation || source.action || '').trim().toLowerCase() || 'create';
        var recordLocalId = source.record_local_id || source.recordLocalId || source.local_id || source.localId || (source.extra && source.extra.local_id) || null;
        var userId = typeof source.user_id !== 'undefined'
            ? source.user_id
            : (typeof source.userId !== 'undefined'
                ? source.userId
                : (global.__OFFLINE_ENGINE_CONFIG__ && typeof global.__OFFLINE_ENGINE_CONFIG__.userId !== 'undefined'
                    ? global.__OFFLINE_ENGINE_CONFIG__.userId
                    : null));
        var syncError = source.sync_error || source.syncError || source.last_error || source.lastError || null;

        if (!tableName && source.url) {
            try {
                var urlPath = normalizeUrl(source.url);
                var urlMatch = urlPath.match(/\/offline\/local\/([^/?#]+)/i) || urlPath.match(/\/([^/?#]+)$/);

                if (urlMatch && urlMatch[1]) {
                    tableName = String(urlMatch[1]).replace(/-/g, '_');
                }
            } catch (error) {
                tableName = tableName || '';
            }
        }

        var normalized = Object.assign({}, source, {
            uuid: source.uuid || generateUuid(),
            request_uuid: source.request_uuid || source.requestUuid || source.uuid || generateUuid(),
            request_key: source.request_key || source.requestKey || buildRequestKey({
                method: source.method,
                url: source.url,
                userId: userId,
                table_name: tableName,
                record_local_id: recordLocalId,
                operation: operation,
                payload: payload
            }),
            url: normalizeUrl(source.url),
            method: normalizeMethod(source.method),
            table_name: tableName,
            tableName: tableName,
            record_local_id: recordLocalId,
            recordLocalId: recordLocalId,
            operation: operation,
            payload: payload,
            formData: payload,
            timestamp: timestamp,
            created_at: source.created_at || source.createdAt || timestamp,
            updated_at: source.updated_at || source.updatedAt || timestamp,
            retry_count: Number(source.retry_count || source.retryCount || 0),
            sync_status: syncStatus,
            queue_state: source.queue_state || source.queueState || (syncStatus === 'synced' ? 'synced' : 'queued'),
            user_id: userId,
            sync_error: syncError,
            last_error: syncError
        });

        return normalized;
    }

    function registerMigration(version, stores, upgrade) {
        var numericVersion = Number(version);

        if (!Number.isInteger(numericVersion) || numericVersion < 1) {
            throw new Error('Migration version must be a positive integer.');
        }

        if (!stores || typeof stores !== 'object') {
            throw new Error('Migration stores must be defined as an object.');
        }

        var normalizedMigration = {
            version: numericVersion,
            stores: stores,
            upgrade: typeof upgrade === 'function' ? upgrade : null
        };

        var existingIndex = MIGRATIONS.findIndex(function (migration) {
            return migration.version === numericVersion;
        });

        if (existingIndex >= 0) {
            MIGRATIONS[existingIndex] = normalizedMigration;
        } else {
            MIGRATIONS.push(normalizedMigration);
        }

        MIGRATIONS.sort(function (left, right) {
            return left.version - right.version;
        });

        return normalizedMigration;
    }

    function getMigrations() {
        return MIGRATIONS.slice();
    }

    function getLatestMigrationVersion() {
        if (!MIGRATIONS.length) {
            return 1;
        }

        return MIGRATIONS[MIGRATIONS.length - 1].version;
    }

    function buildMigrationChain(database) {
        getMigrations().forEach(function (migration) {
            var version = database.version(migration.version).stores(migration.stores);

            if (migration.upgrade) {
                version.upgrade(migration.upgrade);
            }
        });
    }

    function createDatabase() {
        if (!global.Dexie) {
            throw new Error('Dexie.js is required before creating the offline database.');
        }

        var database = new global.Dexie(DATABASE_NAME);
        buildMigrationChain(database);

        return database;
    }

    function getDatabase() {
        if (!readyPromise) {
            databaseInstance = createDatabase();
            readyPromise = databaseInstance.open().then(function () {
                return databaseInstance;
            });
        }

        return readyPromise;
    }

    function registerBusinessStoreDefinition(name, definition) {
        var storeName = String(name || '').trim();

        if (!storeName) {
            throw new Error('Business store name is required.');
        }

        BUSINESS_STORE_DEFINITIONS[storeName] = Object.assign({
            name: storeName,
            tableName: storeName,
            label: storeName,
            displayName: storeName,
            repositoryClassName: null,
            offlineEnabled: true,
            enabled: true,
            searchFields: ['name'],
            primaryFields: ['name'],
            referenceFields: [],
            indexes: [],
            schemaFields: [],
            orderBy: 'updated_at',
            defaults: {},
            requestUrl: '/offline/local/' + storeName
        }, definition || {});

        return BUSINESS_STORE_DEFINITIONS[storeName];
    }

    function getBusinessStoreDefinition(name) {
        return BUSINESS_STORE_DEFINITIONS[String(name || '').trim()] || null;
    }

    function getBusinessStoreNames() {
        return Object.keys(BUSINESS_STORE_DEFINITIONS);
    }

    function normalizeBusinessRecord(storeName, input, overrides) {
        var definition = getBusinessStoreDefinition(storeName) || {};
        var source = Object.assign({}, definition.defaults || {}, input || {}, overrides || {});
        var timestamp = source.updated_at || source.updatedAt || nowIso();
        var createdAt = source.created_at || source.createdAt || timestamp;
        var localId = source.local_id || source.localId || source.server_id || source.serverId || (typeof source.id !== 'undefined' ? source.id : generateUuid());
        var serverId = typeof source.server_id !== 'undefined' ? source.server_id : (typeof source.serverId !== 'undefined' ? source.serverId : null);
        var isDeleted = Boolean(source.is_deleted || source.isDeleted);
        var synced = typeof source.synced === 'boolean' ? source.synced : Boolean(serverId);
        var syncStatus = source.sync_status || source.syncStatus || (synced ? 'synced' : 'pending');
        var localAction = source.local_action || source.localAction || (isDeleted ? 'delete' : (serverId ? 'import' : 'create'));
        var syncError = source.sync_error || source.syncError || source.last_error || source.lastError || null;

        return Object.assign({}, source, {
            local_id: localId,
            server_id: serverId,
            synced: synced,
            sync_status: syncStatus,
            created_at: createdAt,
            updated_at: timestamp,
            is_deleted: isDeleted,
            deleted_at: source.deleted_at || source.deletedAt || null,
            local_action: localAction,
            sync_error: syncError,
            last_error: syncError,
            queue_state: source.queue_state || source.queueState || (syncStatus === 'synced' ? 'synced' : 'queued')
        });
    }

    function buildLocalChangeRequest(storeName, operation, record, changes) {
        var definition = getBusinessStoreDefinition(storeName) || {};
        var normalizedOperation = String(operation || 'create').toLowerCase();
        var methodMap = {
            create: 'POST',
            update: 'PUT',
            delete: 'DELETE'
        };
        var normalizedChanges = isPlainObject(changes) ? sortObject(changes) : (changes || {});
        var localId = record && record.local_id ? record.local_id : null;
        var serverId = record && typeof record.server_id !== 'undefined' ? record.server_id : null;
        var payload = {
            request_fingerprint: stableStringify({
                store: storeName,
                operation: normalizedOperation,
                local_id: localId,
                server_id: serverId,
                changes: normalizedChanges
            }),
            store: storeName,
            operation: normalizedOperation,
            table_name: storeName,
            tableName: storeName,
            local_id: localId,
            localId: localId,
            record_local_id: localId,
            recordLocalId: localId,
            server_id: serverId,
            serverId: serverId,
            changes: normalizedChanges,
            metadata: {
                source: 'offline-repository',
                store_label: definition.label || storeName
            }
        };

        return {
            uuid: generateUuid(),
            request_uuid: generateUuid(),
            url: definition.requestUrl || ('/offline/local/' + storeName),
            method: methodMap[normalizedOperation] || 'POST',
            table_name: storeName,
            tableName: storeName,
            record_local_id: localId,
            recordLocalId: localId,
            operation: normalizedOperation,
            payload: payload,
            formData: payload,
            requestType: 'local-database',
            source: 'offline-repository',
            action: normalizedOperation,
            formName: storeName,
            userId: global.__OFFLINE_ENGINE_CONFIG__ && global.__OFFLINE_ENGINE_CONFIG__.userId ? global.__OFFLINE_ENGINE_CONFIG__.userId : null,
            extra: {
                storeName: storeName,
                operation: normalizedOperation,
                local_id: record && record.local_id ? record.local_id : null,
                server_id: record && typeof record.server_id !== 'undefined' ? record.server_id : null
            }
        };
    }

    function setSetting(key, value) {
        return getDatabase().then(function (database) {
            return database.settings.put({
                key: String(key),
                value: JSON.stringify(value),
                updated_at: nowIso()
            });
        });
    }

    function getSetting(key, fallbackValue) {
        return getDatabase().then(function (database) {
            return database.settings.get(String(key)).then(function (record) {
                if (!record) {
                    return fallbackValue;
                }

                try {
                    return JSON.parse(record.value);
                } catch (error) {
                    return record.value;
                }
            });
        });
    }

    registerMigration(1, {
        pending_requests: '++id,&uuid,&request_key,url,method,user_id,sync_status,timestamp,created_at,updated_at,retry_count',
        sync_queue: '++id,&uuid,&request_uuid,&request_key,status,user_id,created_at,updated_at,retry_count',
        settings: '&key,updated_at'
    });

    registerBusinessStoreDefinition('customers', {
        label: 'Customers',
        searchFields: ['name', 'phone', 'customer_code', 'email', 'address', 'city'],
        orderBy: 'updated_at',
        requestUrl: '/offline/local/customers'
    });

    registerBusinessStoreDefinition('products', {
        label: 'Products',
        searchFields: ['name', 'barcode', 'sku', 'category', 'unit'],
        orderBy: 'updated_at',
        requestUrl: '/offline/local/products'
    });

    registerBusinessStoreDefinition('store', {
        label: 'Store',
        searchFields: ['key', 'code', 'name', 'type', 'value'],
        orderBy: 'updated_at',
        requestUrl: '/offline/local/store'
    });

    registerBusinessStoreDefinition('fuel_sales', {
        label: 'Fuel Sales',
        searchFields: ['ticket_number', 'invoice_number', 'customer_name', 'vehicle_number', 'product_name'],
        orderBy: 'updated_at',
        requestUrl: '/offline/local/fuel-sales'
    });

    registerBusinessStoreDefinition('sales_transactions', {
        label: 'Sales Transactions',
        searchFields: ['transaction_no', 'invoice_no', 'customer_name'],
        orderBy: 'updated_at',
        requestUrl: '/offline/local/sales-transactions'
    });

    registerMigration(2, {
        customers: '++id,&local_id,server_id,name,phone,customer_code,email,address,city,created_at,updated_at,synced,sync_status,local_action,is_deleted,deleted_at',
        products: '++id,&local_id,server_id,name,barcode,sku,category,unit,created_at,updated_at,synced,sync_status,local_action,is_deleted,deleted_at',
        store: '++id,&local_id,server_id,key,code,name,type,value,created_at,updated_at,synced,sync_status,local_action,is_deleted,deleted_at',
        fuel_sales: '++id,&local_id,server_id,ticket_number,invoice_number,customer_id,customer_name,vehicle_number,product_id,product_name,quantity,amount,sale_date,created_at,updated_at,synced,sync_status,local_action,is_deleted,deleted_at',
        sales_transactions: '++id,&local_id,server_id,transaction_no,invoice_no,customer_id,customer_name,total,created_at,updated_at,synced,sync_status,local_action,is_deleted,deleted_at'
    });

    registerConfiguredBusinessStoreDefinitions();

    registerMigration(3, buildBusinessMigrationStores());
    registerMigration(4, buildBusinessMigrationStores());
    registerMigration(5, buildBusinessMigrationStores());
    registerMigration(6, buildBusinessMigrationStores());
    registerMigration(7, buildSyncMigrationStores());
    registerMigration(8, buildSyncMigrationStores());

    global.StoreManagementOfflineDatabase = {
        name: DATABASE_NAME,
        getVersion: getLatestMigrationVersion,
        version: getLatestMigrationVersion(),
        nowIso: nowIso,
        generateUuid: generateUuid,
        normalizeUrl: normalizeUrl,
        formDataToObject: formDataToObject,
        stableStringify: stableStringify,
        buildRequestKey: buildRequestKey,
        getBusinessTableConfig: getBusinessTableConfig,
        buildBusinessStoreSchema: buildBusinessStoreSchema,
        buildBusinessMigrationStores: buildBusinessMigrationStores,
        normalizeRequest: normalizeRequest,
        normalizeRecord: normalizeRequest,
        normalizeBusinessRecord: normalizeBusinessRecord,
        buildLocalChangeRequest: buildLocalChangeRequest,
        registerMigration: registerMigration,
        getMigrations: getMigrations,
        registerBusinessStoreDefinition: registerBusinessStoreDefinition,
        registerConfiguredBusinessStoreDefinitions: registerConfiguredBusinessStoreDefinitions,
        getBusinessStoreDefinition: getBusinessStoreDefinition,
        getBusinessStoreNames: getBusinessStoreNames,
        getDatabase: getDatabase,
        setSetting: setSetting,
        getSetting: getSetting
    };
})(window);
