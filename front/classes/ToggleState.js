export default class ToggleState {

    constructor(visible = false) {
        this.visible = visible
    }

    display(value) {
        this.visible = value
    }

    toggle() {
        this.visible = !this.visible
    }

    show() {
        this.visible = true
    }

    hide() {
        this.visible = false
    }
}
