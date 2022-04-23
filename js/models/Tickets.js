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
        timeSpent = result ?? null;
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
    hasChanged(ids) {
        const idsString = ids.join(',')
        return !loading.ids || (loading.ids !== idsString)
    },
    isLoading() {
        return loading !== null;
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
