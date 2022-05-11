import m from "mithril"
import Alerts from "./Alerts";

let content = []

let timeSpent = {
    time: "",
    release: {
        name: "",
        publicationTimestamp: 0,
        timeSinceRelease: "",
    }
}

const loading = {
    ids: "",
    promise: null,
    xhr: [],
}

function fetchTickets(idsString, projectId) {
    return m.request({
        method: "GET",
        url: `/plugin.php`,
        params: {
            page: "TicketList/api",
            action: "ticket",
            id: idsString,
            projectId,
        },
        withCredentials: true,
        config: function(xhr) {
            loading.xhr.push(xhr)  // needed in order to cancel the request
        },
    }).then(function(result) {
        content = result.tickets ?? [];
        if (result.message) {
            Alerts.add(result.message, 5000)
        }
        return content;
    }).catch(function() {
        Alerts.add(`Erreur en lisant l'api /ticket (ids ${idsString})`, 0)
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
        Alerts.add(`Erreur en lisant l'api /ticket/time (ids ${idsString})`, 0)
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
            content = []
            timeSpent = null
            return Promise.resolve([])
        }
        if (loading.promise !== null) {
            if (loading.ids === idsString) {
                return loading.promise;
            }
            if (loading.xhr.length > 0) {
                for (const xhr of loading.xhr) {
                    xhr.abort()
                }
                loading.xhr = []
                loading.promise = null
            }
        }
        loading.ids = idsString
        const requests = [fetchTickets(idsString, projectId)];
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
