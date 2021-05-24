
export default class Auth {

    constructor() {
        this.conf = {}
        this.user = null
        this.token = null
        this.owner = null
        this.getMeResolve = null
        this.getMeReject = null
        this.offline = false
    }

    config(conf) {
        this.conf = conf
    }
    
    // In most of the functions below, we don't have access to 'this'
    // because functions are directly attached to the routes object of Vue Router.

    ifNotAuthenticated(to, from, next) {
        // We don't pass "next" because we don't want to check the owner here.
        Vue.prototype.$auth.getMe()
        .then(resp => {
            next({ name: 'home' })
        })
        .catch(err => {
            next()
        })
    }

    ifAuthenticated(to, from, next, offlineRoute) {
        Vue.prototype.$auth.getMe(true)
        .then(resp => {
            if (offlineRoute && Vue.prototype.$auth.offline) {
                next({ name: offlineRoute });
            } else {
                next()
            }
        })
        .catch(err => {
            next({ name: err });
        })
    }

    ifHasPermission(permission, next, offlineRoute) {
        Vue.prototype.$auth.getMe(true)
        .then(resp => {
            if (Vue.prototype.$auth.hasPermission(permission)) {
                if (offlineRoute && Vue.prototype.$auth.offline) {
                    next({ name: offlineRoute });
                } else {
                    next()
                }
            } else {
                next({ name: 'unauthorized' });
            }
        })
        .catch(err => {
            next({ name: err });
        })
    }

    ifHasOnePermission(permission, next, offlineRoute) {
        Vue.prototype.$auth.getMe(true)
        .then(resp => {
            if (Vue.prototype.$auth.hasOnePermission(permission)) {
                if (offlineRoute && Vue.prototype.$auth.offline) {
                    next({ name: offlineRoute });
                } else {
                    next()
                }
            } else {
                next({ name: 'unauthorized' });
            }
        })
        .catch(err => {
            next({ name: err });
        })
    }

    ifHasAllPermissions(permission, next, offlineRoute) {
        Vue.prototype.$auth.getMe(true)
        .then(resp => {
            if (Vue.prototype.$auth.hasAllPermissions(permission)) {
                if (offlineRoute && Vue.prototype.$auth.offline) {
                    next({ name: offlineRoute });
                } else {
                    next()
                }
            } else {
                next({ name: 'unauthorized' });
            }
        })
        .catch(err => {
            next({ name: err });
        })
    }

    ifHasNoOwner(to, from, next) {

        // We reset the local storage because want to select a new owner.
        Vue.prototype.$auth.reset()

        // We don't pass "next" because we don't want to check the owner here.
        Vue.prototype.$auth.getMe()
        .then(resp => {
            if (!Vue.prototype.$auth.user.owner) {
                // Only users whithout assigned owner can select a owner.
                next();
            } else {
                next({ name: 'unauthorized' });
            }
        })
        .catch(err => {
            next({ name: err });
        })
    }

    // Make the "GET ME" request and return a promise.
    // When "checkOwner" is set, check that the user has a selected owner.
    // If not, it redirects to the owners selection page.

    getMe(checkOwner = false) {

        // Offline mode.
        // We don't fetch ME anymore.
        if (Vue.prototype.$auth.offline) {
            return {
                then(callback) {
                    callback()
                    return { catch(callback) {} }
                }
            }
        }

        // Request.
        axios.get('/trax/api/front/users/me', {params: {
            accessors: ['permissions'],
            relations: ['owner', 'entity', 'role'],
            include: ['owners', 'csrf-token', 'ui-config'],
        }})
        .then(resp => {

            // Keep user data and XSRF token.
            Vue.prototype.$auth.user = resp.data.data
            Vue.prototype.$auth['csrf-token'] = resp.data.included['csrf-token']
            Vue.prototype.$auth['ui-config'] = resp.data.included['ui-config']
            Vue.prototype.$auth.offline = resp.data.data.offline == true

            // Does the user need to select an owner?
            if (checkOwner && !Vue.prototype.$auth.hasLocalOwner(resp.data.included.owners)) {
                Vue.prototype.$auth.getMeReject('owners')
            } else {
                // Next callback of the Promise.
                Vue.prototype.$auth.getMeResolve()
            }
        })
        .catch(err => {

            // We reset local data.
            Vue.prototype.$auth.reset()

            // We reject the Promise.
            Vue.prototype.$auth.getMeReject('login')
        })

        // Return a promise.
        return new Promise((resolve, reject) => {
            Vue.prototype.$auth.getMeResolve = resolve
            Vue.prototype.$auth.getMeReject = reject
        })
    }

    hasLocalOwner(owners) {
        
        // There is no need to select a owner. It's only a config option.
        if (!Vue.prototype.$auth.conf.splitOwners) {
            return true
        }

        // We load data from the local storage.
        Vue.prototype.$auth.load()

        // The user is attached to an owner and can't select an owner.
        if (Vue.prototype.$auth.user.owner) {
            Vue.prototype.$auth.owner = Vue.prototype.$auth.user.owner
            return true
        }
        
        // Check that the the owner is still valid regarding available owners in the platform.
        if (Vue.prototype.$auth.owner) {
            return Object.keys(owners).find(ownerId => ownerId == Vue.prototype.$auth.owner.id)
        }
        
        // No owner has been found.
        return false
    }

    hasPermission(permission) {
        if (!Vue.prototype.$auth.user) {
            return false
        }
        if (Vue.prototype.$auth.user.admin) {
            return true
        }
        return Vue.prototype.$auth.user.permissions.indexOf(permission) >= 0
    }

    hasOnePermission(permissions) {
        if (!Vue.prototype.$auth.user) {
            return false
        }
        if (Vue.prototype.$auth.user.admin) {
            return true
        }
        let commonPermissions = Vue.prototype.$auth.user.permissions.filter(x => permissions.includes(x))
        return commonPermissions.length > 0
    }

    hasAllPermissions(permissions) {
        if (!Vue.prototype.$auth.user) {
            return false
        }
        if (Vue.prototype.$auth.user.admin) {
            return true
        }
        let missingPermissions = Vue.prototype.$auth.user.permissions.filter(x => !permissions.includes(x));
        return missingPermissions.length == 0
    }

    reset() {
        /* 
        Don't do that because it will cause issues in Vue components which are using user
        just before the components are unloaded!
        Vue.prototype.$auth.user = null
        Vue.prototype.$auth.token = null
        */
        Vue.prototype.$auth.owner = null
        Vue.prototype.$auth.save()
    }

    save() {
        let data = {
            owner: Vue.prototype.$auth.owner,
        }
        localStorage.setItem('auth', JSON.stringify(data))
    }

    load() {
        let data = JSON.parse(localStorage.getItem('auth'))
        Vue.prototype.$auth.owner = data.owner
    }
}
