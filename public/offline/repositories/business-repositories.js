(function (global) {
    'use strict';

    var namespace = global.StoreManagementOfflineRepositories;
    var tableConfig = global.StoreManagementBusinessTableConfig || {};

    if (!namespace || !namespace.OfflineRepository) {
        throw new Error('repository.js must be loaded before business-repositories.js.');
    }

    function toPascalCase(value) {
        return String(value || '')
            .replace(/[_-]+/g, ' ')
            .replace(/[^a-zA-Z0-9 ]+/g, ' ')
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .map(function (segment) {
                return segment.charAt(0).toUpperCase() + segment.slice(1);
            })
            .join('');
    }

    function resolveRepositoryClassName(storeName, definition) {
        if (definition && definition.repositoryClassName) {
            return definition.repositoryClassName;
        }

        return toPascalCase(storeName) + 'Repository';
    }

    function createRepositoryClass(storeName, definition) {
        var resolvedDefinition = definition || {};

        function GenericBusinessRepository(options) {
            options = options || {};

            namespace.OfflineRepository.call(this, storeName, Object.assign({
                searchFields: resolvedDefinition.searchFields || ['name'],
                orderBy: resolvedDefinition.orderBy || 'updated_at',
                trackChanges: resolvedDefinition.trackChanges !== false
            }, options));
        }

        GenericBusinessRepository.prototype = Object.create(namespace.OfflineRepository.prototype);
        GenericBusinessRepository.prototype.constructor = GenericBusinessRepository;

        GenericBusinessRepository.prototype.getDefinition = function () {
            return resolvedDefinition;
        };

        GenericBusinessRepository.prototype.isEnabled = function () {
            return resolvedDefinition.offlineEnabled !== false && resolvedDefinition.enabled !== false;
        };

        return GenericBusinessRepository;
    }

    function registerConfiguredRepositories() {
        Object.keys(tableConfig).forEach(function (storeName) {
            var definition = tableConfig[storeName] || {};

            if (definition.offlineEnabled === false || definition.enabled === false) {
                return;
            }

            if (namespace.getRepository(storeName)) {
                return;
            }

            var RepositoryClass = createRepositoryClass(storeName, definition);
            var className = resolveRepositoryClassName(storeName, definition);

            namespace.registerRepository(storeName, RepositoryClass);
            namespace[storeName] = RepositoryClass;

            if (!global[className]) {
                global[className] = RepositoryClass;
            }

            if (!global['StoreManagement' + className]) {
                global['StoreManagement' + className] = RepositoryClass;
            }
        });
    }

    namespace.createBusinessRepositoryClass = createRepositoryClass;
    namespace.registerConfiguredRepositories = registerConfiguredRepositories;

    global.StoreManagementBusinessRepositories = namespace;

    registerConfiguredRepositories();
})(window);
