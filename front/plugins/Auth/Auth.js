
export default class Auth {

    constructor() {
        this.conf = {}
        this.user = null
        this.owner = null
    }

    config(conf) {
        this.conf = conf
    }
    
    ifNotAuthenticated(to, from, next) {
        // We don't have access to 'this' from this method because
        // it is called directly from Vue Router.
        axios.get('/trax/api/front/users/me', {params: {
            accessors: ['permissions'],
            relations: ['owner', 'entity', 'role'],
            include: ['owners'],
        }})
        .then(resp => {
            Vue.prototype.$auth.user = resp.data.data
            next({ name: 'home' })
        })
        .catch(err => {
            Vue.prototype.$auth.user = null
            Vue.prototype.$auth.reset()
            next()
        })
    }

    ifAuthenticated(to, from, next) {
        // We don't have access to 'this' from this method because
        // it is called directly from Vue Router.
        axios.get('/trax/api/front/users/me', {params: {
            accessors: ['permissions'],
            relations: ['owner', 'entity', 'role'],
            include: ['owners'],
        }})
        .then(resp => {
            Vue.prototype.$auth.user = resp.data.data
            Vue.prototype.$auth.checkOwner(resp.data.included.owners, next)
        })
        .catch(err => {
            next({ name: 'login' });
        })
    }

    ifHasPermission(permission, next) {
        // We don't have access to 'this' from this method because
        // it is called directly from Vue Router.
        axios.get('/trax/api/front/users/me', {params: {
            accessors: ['permissions'],
            relations: ['owner', 'entity', 'role'],
            include: ['owners'],
        }})
        .then(resp => {
            Vue.prototype.$auth.user = resp.data.data
            if (Vue.prototype.$auth.hasPermission(permission)) {
                Vue.prototype.$auth.checkOwner(resp.data.included.owners, next)
            } else {
                next({ name: 'unauthorized' });
            }
        })
        .catch(err => {
            next({ name: 'login' });
        })
    }

    ifHasNoOwner(to, from, next) {
        // We don't have access to 'this' from this method because
        // it is called directly from Vue Router.

        // We reset the local storage because we just exited from an owner.
        Vue.prototype.$auth.reset()

        axios.get('/trax/api/front/users/me', {params: {
            accessors: ['permissions'],
            relations: ['owner', 'entity', 'role'],
            include: ['owners'],
        }})
        .then(resp => {
            Vue.prototype.$auth.user = resp.data.data
            if (!Vue.prototype.$auth.user.owner) {
                next();
            } else {
                next({ name: 'unauthorized' });
            }
        })
        .catch(err => {
            next({ name: 'login' });
        })
    }

    checkOwner(owners, next) {
        
        if (!Vue.prototype.$auth.conf.splitOwners) {
            next()
            return
        }

        // We load data from the local storage.
        Vue.prototype.$auth.load()

        if (Vue.prototype.$auth.user.owner) {
            // User with an owner.
            // No need to save it again!
            Vue.prototype.$auth.owner = Vue.prototype.$auth.user.owner
            next()

        } else if (Vue.prototype.$auth.owner) {
            // Owner already selected.
            // We check it still exists...
            let found = Object.keys(owners).find(ownerId => ownerId == Vue.prototype.$auth.owner.id)
            if (found) {
                // It still exists. No need to save it again!
                next()
            } else {
                // Otherwise, go to the owners page.
                next({ name: 'owners' })
            }

        } else {
            // Otherwise, go to the owners page.
            next({ name: 'owners' })
        }
    }

    hasPermission(permission) {
        return Vue.prototype.$auth.user.admin || Vue.prototype.$auth.user.permissions.indexOf(permission) >= 0
    }

    reset() {
        Vue.prototype.$auth.owner = null
        Vue.prototype.$auth.save()
    }

    load() {
        let data = JSON.parse(localStorage.getItem('auth'))
        Vue.prototype.$auth.owner = data.owner
    }

    save() {
        let data = {
            owner: Vue.prototype.$auth.owner,
        }
        localStorage.setItem('auth', JSON.stringify(data))
    }
}
