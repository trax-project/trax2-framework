export default class XapiStatementsLoader {

    constructor(endpoint, filters) {
        this.endpoint = endpoint
        this.firstLoading = false
        this.nextLoading = false
        this.noMore = false
        this.rows = []
        this.lastId = null
        this.filters = filters.statements
        this.params = {}
        this.pageSize = 20
    }

    setMapData(mapData) {
        this.mapData = mapData
    }

    baseParams() {
        return {
            limit: this.pageSize,
            filters: {
                voided: false
            },
            options: {}
        }
    }

    firstLoad() {
        let params = this.filters.get(this.baseParams())
        if (params === false) {
            return
        }
        this.params = params
        this.firstLoading = true
        this.rows = []
        this.lastId = null
        this.load()
    }

    nextLoad() {
        this.nextLoading = true
        this.paginate()
        this.load()
    }

    paginate() {
        if (this.params.sort == 'id') {
            this.params.after = { id: this.lastId }
        } else {
            this.params.before = { id: this.lastId }
        }
    }
    
    load() {
        axios.get(this.endpoint, { 
            params: this.params 
        }).then(resp => {
            let count = this.rows.length
            resp.data.data.forEach((item, index) => {
                this.rows.push(this.mapData(item, count + index))
                this.lastId = item.id
            });
            this.firstLoading = false
            this.nextLoading = false
            this.noMore = count == this.rows.length
        })
    }

    hasMore() {
        return this.filters.chronological || (this.rows.length % this.pageSize == 0 && !this.noMore)
    }
}
