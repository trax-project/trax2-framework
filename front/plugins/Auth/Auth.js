
export default class Auth {

    constructor() {
        this.conf = {}
        this.user = null
        this.token = null
        this.owner = null
        this._redirect = null
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
        Vue.prototype.$auth.getMe(to, from, next)
        .then(resp => {
            next({ name: 'home' })
        })
        .catch(err => {
            next()
        })
    }

    ifAuthenticated(to, from, next, offlineRoute) {
        Vue.prototype.$auth.getMe(to, from, next, true)
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

    ifHasPermission(permission, to, from, next, offlineRoute) {
        Vue.prototype.$auth.getMe(to, from, next, true)
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

    ifHasOnePermission(permissions, to, from, next, offlineRoute) {
        Vue.prototype.$auth.getMe(to, from, next, true)
        .then(resp => {
            if (Vue.prototype.$auth.hasOnePermission(permissions)) {
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

    ifHasAllPermissions(permissions, to, from, next, offlineRoute) {
        Vue.prototype.$auth.getMe(to, from, next, true)
        .then(resp => {
            if (Vue.prototype.$auth.hasAllPermissions(permissions)) {
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
        Vue.prototype.$auth.resetOwner()

        // We don't pass "next" because we don't want to check the owner here.
        Vue.prototype.$auth.getMe(to, from, next)
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

    getMe(to, from, next, checkOwner = false) {

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

            // Reset the redirect.
            Vue.prototype.$auth.resetRedirect()

            // Does the user need to select an owner?
            if (checkOwner && !Vue.prototype.$auth.hasLocalOwner(resp.data.included.owners)) {
                Vue.prototype.$auth.getMeReject('owners')
            } else {
                // Next callback of the Promise.
                Vue.prototype.$auth.getMeResolve()
            }
        })
        .catch(err => {
            Vue.prototype.$auth.handleErrorNext(err, to, from, next)
        })

        // Return a promise.
        return new Promise((resolve, reject) => {
            Vue.prototype.$auth.getMeResolve = resolve
            Vue.prototype.$auth.getMeReject = reject
        })
    }

    handleErrorNext(error, to, from, next) {
        switch (error.response.status) {
            case 401:
            case 419:
                Vue.prototype.$auth.saveRedirect(to)
                Vue.prototype.$auth.resetOwner()
                Vue.prototype.$auth.getMeReject('login')
                return true
            case 503:
                Vue.prototype.$auth.saveRedirect(to)
                next({ name: 'maintenance'})
                return true
            default:
                Vue.prototype.$auth.saveRedirect(from)
                next({ name: 'error', params: { status: error.response.status }})
                return true
        }
    }

    handleErrorVm(error, vm) {
        switch (error.response.status) {
            case 401:
            case 419:
                Vue.prototype.$auth.saveRedirect(vm.$route)
                Vue.prototype.$auth.resetOwner()
                vm.$router.push({ name: 'login'})
                return true
            case 503:
                Vue.prototype.$auth.saveRedirect(vm.$route)
                vm.$router.push({ name: 'maintenance'})
                return true
            default:
                Vue.prototype.$auth.saveRedirect(vm.$route)
                vm.$router.push({ name: 'error', params: { status: error.response.status }})
                return true
        }
    }

    hasLocalOwner(owners) {
        
        // There is no need to select a owner. It's only a config option.
        if (!Vue.prototype.$auth.conf.splitOwners) {
            return true
        }

        // We load data from the local storage.
        Vue.prototype.$auth.loadOwner()

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
        let foundPermissions = Vue.prototype.$auth.user.permissions.filter(x => permissions.includes(x))
        return foundPermissions.length > 0
    }

    hasAllPermissions(permissions) {
        if (!Vue.prototype.$auth.user) {
            return false
        }
        if (Vue.prototype.$auth.user.admin) {
            return true
        }
        let foundPermissions = Vue.prototype.$auth.user.permissions.filter(x => permissions.includes(x))
        return foundPermissions.length == permissions.length
    }

    resetOwner() {
        Vue.prototype.$auth.owner = null
        Vue.prototype.$auth.saveOwner()
    }

    saveOwner() {
        let data = {
            owner: Vue.prototype.$auth.owner,
        }
        localStorage.setItem('auth', JSON.stringify(data))
    }

    loadOwner() {
        let data = JSON.parse(localStorage.getItem('auth'))
        Vue.prototype.$auth.owner = data.owner
    }

    redirect() {
        if (Vue.prototype.$auth._redirect && Vue.prototype.$auth._redirect.name) {
            return Vue.prototype.$auth._redirect
        }
        return null
    }

    resetRedirect() {
        Vue.prototype.$auth._redirect = null
    }

    saveRedirect(to) {
        if (['login', 'maintenance', 'error', 'unknown', 'unauthorized'].includes(to.name)) {
            return
        }
        Vue.prototype.$auth._redirect = to
    }
}
