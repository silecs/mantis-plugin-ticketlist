import m from "mithril"

let content = []

let timeSpent = {
    time: "",
    timeSinceRelease: "",
    release: {
        name: "",
        publicationTimestamp: 0,
    }
}

const loading = {
    ids: "",
    promise: null,
    xhr: [],
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

function fetchTickets(idsString) {
    return m.request({
        method: "GET",
        url: `/plugin.php`,
        params: {
            page: "TicketList/api",
            action: "ticket",
            id: idsString,
        },
        withCredentials: true,
        config: function(xhr) {
            loading.xhr.push(xhr)  // needed in order to cancel the request
        },
    }).then(function(result) {
        content = result ?? [];
        return content;
    }).catch(function() {
        alert(`Erreur en lisant l'api /ticket (ids ${idsString})`)
    });
}

function fetchTicketsTime(idsString, projectId) {
    return m.request({
        method: "GET",
        url: `/plugin.php`,
        params: {
            page: "TicketList/api",
            action: "ticket/time",
            id: idsString,
            projectId,
        },
        withCredentials: true,
        config: function(xhr) {
            loading.xhr.push(xhr)  // needed in order to cancel the request
        },
    }).then(function(result) {
        timeSpent = result ?? [];
        return timeSpent;
    }).catch(function() {
        alert(`Erreur en lisant l'api /ticket/time (ids ${idsString})`)
    });
}

export default {
    get() {
        return content;
    },
    getTimeSpent() {
        return timeSpent;
    },
    isLoading() {
        return loading !== null;
    },
    loadFromText(str, projectId) {
        const ids = parseIdsText(str)
        return this.load(ids, projectId)
    },
    load(ids, projectId) {
        const idsString = ids.join(',')
        if (idsString === '') {
            return Promise.resolve([])
        }
        if (loading.promise !== null) {
            if (loading.ids === idsString) {
                return loading.promise;
            }
            if (loading.xhr.length > 0) {
                for (let xhr of loading.xhr) {
                    xhr.abort()
                }
                loading.xhr = []
                loading.promise = null
            }
        }
        loading.ids = idsString
        const requests = [fetchTickets(idsString)];
        if (projectId > 0) {
            requests.push(fetchTicketsTime(idsString, projectId));
        } else {
            timeSpent = {}
        }
        loading.promise = Promise.all(requests).finally(function() {
            loading.promise = null;
            loading.xhr = []
        })
        return loading.promise
    }
}
