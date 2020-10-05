
export default class FormErrors {

    constructor() {
        this.clearAll()
    }

    // Set a local validator like Vee and give the VM.
    
    setValidator(validator, vm) {
        this.validator = validator
        this.vm = vm
    }

    // Use this function to remove all errors.
    // This is mandatory before any local validation.

    clearAll() {
        this.errors = {}
        this.temp = {}
        if (this.vm && this.validator) {
            this.vm.$nextTick(() => this.vm.$refs[this.validator].reset())
        }
    }

    // This function is typically used with server validation.
    // Errors is an object with a property for each field,
    // and a list of errors for each property.

    set(errors) {
        this.errors = errors
    }

    // This function is typically used with local validation.
    // On a cleared form, each field is checked individually,
    // and errors are added one by one. At the end, the added()
    // function is used to check if there are errors.
    // The temp property is used to preserve reactivity.

    add(field, message) {
        this.temp[field] = [message]
    }

    added() {
        this.errors = this.temp
        return this.any()
    }

    // Use these functions on the error section of each field.
    // has() can be used to display or hide the error section.
    // get() will be used to get the 1st error message.

    has(field) {
        return this.errors.hasOwnProperty(field)
    }

    get(field) {
        if (this.has(field)) {
            return this.errors[field][0]
        }
    }

    // This function is typically used to clear the error message
    // of a single field when this field is being modified.

    clear(field) {
        delete this.errors[field]
    }

    // This function is typically used to check there is no error
    // before making a request.

    any() {
        return Object.keys(this.errors).length > 0
    }
}
