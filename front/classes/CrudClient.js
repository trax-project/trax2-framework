import Axios from "axios"

export default class CrudClient {

    constructor(endpoint, query) {
        this.endpoint = endpoint
        this.query = query === undefined ? {} : query
    }

    list(paging) {
        if (paging !== undefined && paging.perPage) {
            this.query.limit = paging.perPage
            this.query.skip = (paging.currentPage - 1) * paging.perPage
        }
        return axios.get(this.endpoint, { params: this.query })
    }

    read(id) {
        return axios.get(this.endpoint+'/'+id, { params: this.query })
    }

    create(data) {
        return axios.post(this.endpoint, data)
    }

    update(data) {
        return axios.put(this.endpoint+'/'+data.id, data)
    }

    delete(id) {
        return axios.delete(this.endpoint+'/'+id)
    }
}