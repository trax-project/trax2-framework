export default class ModalsController {

    constructor(ids) {
        this.promises = {}
        if (typeof ids === 'string') {
            this.ids = [ids]
        } else {
            this.ids = ids
        }
        this.ids.forEach(id => {
            this[id] = { id, show: false }
            this.promises[id] = {}
        })
    }

    init(component, target) {
        this.vm = component
        this.watch(component, target)
    }

    open(id) {
        this[id] = { id: this[id].id, show: true }
        return new Promise((resolve, reject) => {
            this.promises[id].resolve = resolve
            this.promises[id].reject = reject
        })
    }

    close(id) {
        this[id] = { id: this[id].id, show: false }
    }

    watch(component, target) {
        this.ids.forEach(id => {
            component.$watch(target + '.' + id, (modal) => {
                if (!modal.show) {
                    this.promises[id].resolve(modal)
                }
            })
        })
    }
}