# SampleTenancyListeners
Sample Listeners from a Personal project that use the Tenancy/Tenancy framework to implement basic SaaS functionality

## Overview
The following code is the App/Tenancy namespace from a personal project. (Excluding the Organization class)

The code posted here is current development code as of 02MAY2021.

### Composer .JSON

```json
	"tenancy/affects-configs": "^1.3",
        "tenancy/affects-connections": "^1.3",
        "tenancy/affects-routes": "^1.3",
        "tenancy/db-driver-mysql": "^1.3",
        "tenancy/framework": "^1.3",
        "tenancy/hooks-database": "^1.3",
        "tenancy/hooks-migration": "^1.3",
        "tenancy/identification-driver-console": "^1.3",
        "tenancy/identification-driver-http": "^1.3",
        "tenancy/identification-driver-queue": "^1.3",
```

### Tenant Model
My tenant model is the "Organization" class. Which I have provided simply to demonstrate some advance possibilities.

### DATABASE NOTE
Please note, that I have 2 connections configured by default in my Laravel application.
The connection referenced in the listeners is my "Admin" user which can create users, databases, and grant privilages to users.