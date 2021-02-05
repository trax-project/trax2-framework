import FormErrors from './FormErrors';

export default class CrudForm {

    constructor(crudClient, mapData, unmapData) {
        this.client = crudClient
        if (mapData) {
            this.mapData = mapData
        }
        if (unmapData) {
            this.unmapData = unmapData
        }
        this.errors = new FormErrors()
        this.reset()
    }

    init(component, target) {
        this.vm = component
        this.watch(component, target)
    }

    mapData(data = {}) {
        return data
    }

    unmapData(data = {}) {
        return data
    }

    reset(data) {
        this.errors.clearAll()
        this.data = data ? data : this.mapData()
        this.reseting = true
        this.creating = false
        this.updating = false
        this.duplicating = false
        this.loading = false
        this.recording = false
    }

    create(data) {
        this.reset(data)
        this.creating = true
        return new Promise((resolve) => {
            this.resolve = resolve
        })
    }

    created() {
        this.errors.clearAll()
        this.recording = true
        this.client.create(this.unmapData(this.data))
            .then(resp => {
                this.recording = false
                this.resolve(resp)
            })
            .catch(err => {
                this.recording = false
                if (err.response.status == 422) {
                    this.errors.set(err.response.data.errors)
                }
            })
    }

    update(idOrObject) {
        this.reset()
        if (typeof idOrObject === 'object') {
            this.data = this.mapData(idOrObject)
            this.updating = true
        } else {
            this.loading = true
            this.client.read(idOrObject).then(resp => {
                this.data = this.mapData(resp.data.data)
                this.updating = true
            })
        }
        return new Promise((resolve) => {
            this.resolve = resolve
        })
    }

    updated() {
        this.errors.clearAll()
        this.recording = true
        this.client.update(this.unmapData(this.data))
            .then(resp => {
                this.recording = false
                this.resolve(resp)
            })
            .catch(err => {
                this.recording = false
                if (err.response.status == 422) {
                    this.errors.set(err.response.data.errors)
                }
            })
    }

    duplicate(idOrObject) {
        this.reset()
        if (typeof idOrObject === 'object') {
            this.data = this.mapData(idOrObject)
            this.duplicating = true
        } else {
            this.loading = true
            this.client.read(idOrObject).then(resp => {
                this.data = this.mapData(resp.data.data)
                this.duplicating = true
            })
        }
        return new Promise((resolve) => {
            this.resolve = resolve
        })
    }

    duplicated() {
        this.errors.clearAll()
        this.recording = true
        this.client.duplicate(this.unmapData(this.data))
            .then(resp => {
                this.recording = false
                this.resolve(resp)
            })
            .catch(err => {
                this.recording = false
                if (err.response.status == 422) {
                    this.errors.set(err.response.data.errors)
                }
            })
    }

    watch(component, target) {
        component.$watch(target + '.data', (data) => {
            if (this.reseting) {
                this.reseting = false
            } else if (this.creating) {
                this.created()
            } else if (this.loading) {
                this.loading = false
            } else if (this.updating) {
                this.updated()
            } else if (this.duplicating) {
                this.duplicated()
            }
        })
    }
}
