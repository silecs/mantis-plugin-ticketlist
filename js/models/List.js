import m from "mithril"

const emptyList = {
    id: 0,
    ids: "",
    name: "",
    projectId: 0,
}

let content = emptyList

let loading = null

export default {
    get() {
        return content;
    },
    isLoading() {
        return loading !== null;
    },
    load(id) {
        if (loading !== null) {
            return loading;
        }
        loading = m.request({
            method: "GET",
            url: `/plugin.php`,
            params: {
                page: "TicketList/api",
                action: "list",
                id,
            },
            withCredentials: true,
        }).then(function(result) {
            content = result ?? emptyList;
            return content;
        }).catch(function() {
            alert(`Erreur en lisant l'api /list/${id}`)
        }).finally(function() {
            loading = null;
        });
        return loading
    }
}
