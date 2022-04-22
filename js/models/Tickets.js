import m from "mithril"

let content = []

const loading = {
    ids: "",
    promise: null,
    xhr: null,
}

function parseIdsText(str) {
    const ids = []
    const lines = str.split(/\n/)
    for (let line of lines) {
        if (!line.match(/^\s*\d+/)) {
            continue;
        }
        for (let num of line.split(/[\s,]+/)) {
            const id = parseInt(num, 10)
            if (id > 0) {
                ids.push(id)
            }
        }
    }
    return ids
}

export default {
    get() {
        return content;
    },
    isLoading() {
        return loading !== null;
    },
    loadFromText(str) {
        const ids = parseIdsText(str)
        return this.load(ids)
    },
    load(ids) {
        const idsString = ids.join(',')
        if (idsString === '') {
            return Promise.resolve([])
        }
        if (loading.promise !== null) {
            if (loading.ids === idsString) {
                return loading.promise;
            }
            if (loading.xhr !== null) {
                loading.xhr.abort()
                loading.xhr = null
                loading.promise = null
            }
        }
        loading.ids = idsString
        loading.promise = m.request({
            method: "GET",
            url: `/plugin.php`,
            params: {
                page: "TicketList/api",
                action: "ticket",
                id: idsString,
            },
            withCredentials: true,
            config: function(xhr) { loading.xhr = xhr }, // needed in order to cancel the request
        }).then(function(result) {
            content = result ?? [];
            return content;
        }).catch(function() {
            alert(`Erreur en lisant l'api /ticket (ids ${idsString})`)
        }).finally(function() {
            loading.promise = null;
            loading.xhr = null
        });
        return loading.promise
    }
}
