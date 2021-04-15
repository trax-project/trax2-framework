import XapiLoader from './XapiLoader';

export default class XapiStatementsLoader extends XapiLoader {

    baseFilters() {
        return {
            voided: false
        }
    }

    hasMore() {
        return this.filters.chronological || super.hasMore()
    }
}
