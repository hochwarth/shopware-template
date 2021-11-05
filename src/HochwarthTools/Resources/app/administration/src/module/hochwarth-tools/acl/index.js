Shopware.Service('privileges')
    .addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'settings',
        key: 'hochwarth_tools',
        roles: {
            viewer: {
                privileges: [
                    'hochwarth_tools:read'
                ],
                dependencies: []
            }
        }
    });
