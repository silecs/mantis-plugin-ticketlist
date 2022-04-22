import m from "mithril"

let content = {
    id: 0,
    name: "Tous les projets",
}

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
        if (content.id === id) {
            // Do not query if the project has not changed.
            return Promise.resolve(content)
        }

        loading = m.request({
            method: "GET",
            url: "/plugin.php",
            params: {
                page: "TicketList/api",
                action: "project",
                id,
            },
            withCredentials: true,
        }).then(function(result) {
            state.lists = result;
        }).catch(function() {
            alert(`Erreur en lisant l'api /project/${id}`)
        }).finally(function() {
            loading = null;
        });
        return loading
    },
    readJsonBlock(name) {
        const htmlElement = document.getElementById(name)
        if (htmlElement === null) {
            console.log(`invalid HTML : element #${name} was not found.`)
            return;
        }
        content = JSON.parse(htmlElement.textContent)
    }
}
