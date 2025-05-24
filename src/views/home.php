<div class="text-center mb-5">
    <h1 class="display-4 section-title mx-auto" style="max-width: 800px;">Partagez et explorez des romans, manhwas et animés</h1>
    <p class="lead text-text-light mb-0">Recommandez vos coups de cœur et découvrez de nouvelles œuvres à lire ou à regarder</p>
</div>

<div class="filter-group p-4 mb-5 rounded shadow-sm">
    <form id="searchForm">
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" placeholder="Rechercher un roman, manhwa ou animé..." id="searchInput" name="q">
                    <button class="btn btn-primary" type="button" id="searchButton">Rechercher</button>
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select form-select-lg" name="filterType" id="filterTypeSelect">
                    <option value="all" selected>Tous les types</option>
                    <option value="roman">📖 Roman</option>
                    <option value="manhwa">🖼️ Manhwa</option>
                    <option value="anime">🎬 Animé</option>
                </select>
            </div>
        </div>
    </form>
</div>

<!-- Conteneur de publications -->
<h2 class="section-title mb-4">Publications</h2>
<div class="row g-4" id="publicationsContainer">
    <!-- Les publications seront insérées ici dynamiquement par le JS -->
</div>

<!-- Pagination -->
<nav aria-label="Pagination des publications" class="mt-5">
    <ul class="pagination justify-content-center" id="publicationPagination">
        <!-- La pagination sera générée dynamiquement -->
    </ul>
</nav>

