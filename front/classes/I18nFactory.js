import VueI18n from 'vue-i18n'

Vue.use(VueI18n)

export default class I18nFactory {

    static make(appLocales, libLocales) {
        return new VueI18n({
            locale: 'en',
            fallbackLocale: 'en',
            messages: this.mergeLocales(appLocales, libLocales)
        })
    }

    static mergeLocales(appLocales, libLocales) {
        const messages = {};
        appLocales.keys().forEach(key => {
            const matched = key.match(/([a-z0-9]+)\./i)
            if (matched && matched.length > 1) {
                const lang = matched[1]
                const app = appLocales(key)
                const lib = libLocales(key)
                messages[lang] = this.merge(lib, app)
            }
        })
        return messages
    }

    static merge(target, source) {
        for (const key of Object.keys(source)) {
            if (source[key] instanceof Object) Object.assign(source[key], this.merge(target[key], source[key]))
        }
        Object.assign(target || {}, source)
        return target
    }
}

