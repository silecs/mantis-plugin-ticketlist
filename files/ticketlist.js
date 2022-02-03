window.addEventListener('load', function() {
    var ca = document.querySelector('input.checkall')
    if (ca !== null) {
        ca.addEventListener(
            'click',
            function(e) {
                e.target.parentNode
                    .querySelectorAll('input[type=checkbox][value]')
                    .forEach(function(c) {
                        c.click();
                    });
            }
        );
    }

    function toIntArray(text) {
        return text.split(/[^\d]+/).map(x => parseInt(x, 10)).filter(x => x > 0);
    }

    function onIdsInput() {
        const ids = toIntArray(this.value.trim());
        document.getElementById('publish').innerHTML = (ids.length === 0 ? "Supprimer" : "Publier");
    }
    const inputIds = document.querySelector('form textarea[name="ids"]');
    onIdsInput.apply(inputIds);
    inputIds.addEventListener('input', onIdsInput);

    function onTitleInput() {
        const title = this.value.trim();
        if (title === '') {
            document.getElementById('publish').setAttribute('disabled', 'disabled');
        } else {
            document.getElementById('publish').removeAttribute('disabled');
        }
    }
    const inputTitle = document.querySelector('form input[name="title"]');
    onTitleInput.apply(inputTitle);
    inputTitle.addEventListener('input', onTitleInput);

    document.getElementById('publish').addEventListener(
        'click',
        function(e) {
            const form = this.closest('form');
            const title = form.title.value.trim();
            const ids = toIntArray(form.ids.value.trim());
            if (title === '') {
                return;
            }
            if (!title.match(/^[a-zA-Z0-9_() -]+$/)) {
                alert("Le titre doit être sans accents ni caractères hors 'a-zA-Z0-9_() -'");
                return;
            }
            if (ids.length === 0 && !confirm(`Supprimer « ${title} » ?`)) {
                return;
            }
            fetch(
                "/plugin.php?page=TicketList/save", // hardcoded!
                {
                    method: 'POST',
                    mode: 'same-origin',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        name: title,
                        ids: ids,
                    }),
                }
            ).then(
                response => response.json()
            ).then(function(data) {
                if (data === true) {
                    form.submit();
                } else {
                    alert("Erreur lors de l'enregistrement.");
                }
            });
        }
    );
});
