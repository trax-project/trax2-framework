
export default class Auth {

    constructor() {
        this.conf = {}
        this.user = null
        this.token = null
        this.owner = null
        this.getMeResolve = null
        this.getMeReject = null
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

    ifAuthenticated(to, from, next) {
        Vue.prototype.$auth.getMe(next)
        .then(resp => {
            next()
        })
        .catch(err => {
            next({ name: 'login' });
        })
    }

    ifHasPermission(permission, next) {
        Vue.prototype.$auth.getMe(next)
        .then(resp => {
            if (Vue.prototype.$auth.hasPermission(permission)) {
                next()
            } else {
                next({ name: 'unauthorized' });
            }
        })
        .catch(err => {
            next({ name: 'login' });
        })
    }

    ifHasOnePermission(permission, next) {
        Vue.prototype.$auth.getMe(next)
        .then(resp => {
            if (Vue.prototype.$auth.hasOnePermission(permission)) {
                next()
            } else {
                next({ name: 'unauthorized' });
            }
        })
        .catch(err => {
            next({ name: 'login' });
        })
    }

    ifHasAllPermissions(permission, next) {
        Vue.prototype.$auth.getMe(next)
        .then(resp => {
            if (Vue.prototype.$auth.hasAllPermissions(permission)) {
                next()
            } else {
                next({ name: 'unauthorized' });
            }
        })
        .catch(err => {
            next({ name: 'login' });
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
            next({ name: 'login' });
        })
    }

    // Make the "GET ME" request and return a promise.
    // When "next" is set, check that the user has a selected owner.
    // If not, it redirects to the owners selection page.

    getMe(next = null) {

        // Request.
        axios.get('/trax/api/front/users/me', {params: {
            accessors: ['permissions', 'rights'],
            relations: ['owner', 'entity', 'role'],
            include: ['owners', 'xsrf-token'],
        }})
        .then(resp => {

            // Keep user data and XSRF token.
            Vue.prototype.$auth.user = resp.data.data
            Vue.prototype.$auth['xsrf-token'] = resp.data.included['xsrf-token']

            // Does the user need to select an owner?
            if (next && !Vue.prototype.$auth.hasLocalOwner(resp.data.included.owners)) {
                next({ name: 'owners' })
            } else {
                // Next callback of the Promise.
                Vue.prototype.$auth.getMeResolve()
            }
        })
        .catch(err => {

            // We reset local data.
            Vue.prototype.$auth.reset()

            // We reject the Promise.
            Vue.prototype.$auth.getMeReject()
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

    hasRight(right) {
        if (!Vue.prototype.$auth.user) {
            return false
        }
        // We don't check that the user is an admin here because
        // all rights are not necessarily granted to admins.
        return Vue.prototype.$auth.user.rights[right]
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
