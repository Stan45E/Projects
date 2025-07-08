document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const frequentSitesContainer = document.getElementById('frequentSitesContainer');
    const allSitesContainer = document.getElementById('allSitesContainer');
    const addSiteForm = document.getElementById('addSiteForm');
    const nameInput = document.getElementById('siteNameInput');
    const urlInput = document.getElementById('siteUrlInput');
    const searchInput = document.getElementById('searchInput');
    const addGroupForm = document.getElementById('addGroupForm');
    const groupNameInput = document.getElementById('groupNameInput');
    const groupSelect = document.getElementById('groupSelect');
    const addCurrentTabBtn = document.getElementById('addCurrentTabBtn');
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const exportBtn = document.getElementById('exportBtn');
    const importBtn = document.getElementById('importBtn');
    const importFile = document.getElementById('importFile');
    const toast = document.getElementById('toastNotification');

    // --- SVG Icons ---
    const trashIcon = `<svg viewBox="0 0 24 24"><path d="M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19M8,9H16V19H8V9M15.5,4L14.5,3H9.5L8.5,4H5V6H19V4H15.5Z" /></svg>`;
    const editIcon = `<svg viewBox="0 0 24 24"><path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.13,5.12L18.88,8.87M3,17.25V21H6.75L17.81,9.94L14.06,6.19L3,17.25Z" /></svg>`;
    const dragIcon = `<span>☰</span>`;
    const sunIcon = `<svg viewBox="0 0 24 24"><path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7M12,2L14.39,4.39L17.3,3.34L18.36,6.25L21.25,7.31L20.2,10.2L22,12L20.2,13.8L21.25,16.69L18.36,17.75L17.3,20.66L14.39,19.61L12,22L9.61,19.61L6.7,20.66L5.64,17.75L2.75,16.69L3.81,13.8L2,12L3.81,10.2L2.75,7.31L5.64,6.25L6.7,3.34L9.61,4.39L12,2Z" /></svg>`;
    const moonIcon = `<svg viewBox="0 0 24 24"><path d="M12 2A9.91 9.91 0 0 0 9 2.46A10 10 0 0 1 9 21.54A10 10 0 1 0 12 2Z" /></svg>`;

    // --- Data Store ---
    let websites = [];
    let groups = [];
    let draggedItem = null;
    let toastTimeout;

    // --- Toast Notification Function ---
    const showToast = (message, type = 'success') => {
        clearTimeout(toastTimeout);
        toast.textContent = message;
        toast.className = ''; // Clear previous classes
        toast.classList.add(type);

        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        toastTimeout = setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    };

    // --- Data Handling ---
    const loadData = () => {
        chrome.storage.sync.get(['quickLinks', 'linkGroups', 'theme'], (data) => {
            const theme = data.theme || 'dark';
            document.body.classList.toggle('light-theme', theme === 'light');
            themeToggleBtn.innerHTML = theme === 'light' ? moonIcon : sunIcon;

            groups = (data.linkGroups || [{ id: 1, name: "Uncategorized", isCollapsed: false }])
                .map(g => ({ ...g, isCollapsed: g.isCollapsed || false }));

            websites = (data.quickLinks || []).map((site, index) => ({
                id: site.id || Date.now() + index,
                name: site.name,
                url: site.url,
                clickCount: site.clickCount || 0,
                hiddenFromFrequent: site.hiddenFromFrequent || false,
                groupId: site.groupId || 1,
            }));

            if (!data.linkGroups) {
                chrome.storage.sync.set({ linkGroups: groups }, renderAll);
            } else {
                renderAll();
            }
        });
    };

    const saveData = () => {
        chrome.storage.sync.set({ quickLinks: websites, linkGroups: groups }, renderAll);
    };

    // --- Rendering ---
    const renderAll = () => {
        const filter = searchInput.value.toLowerCase();
        const isSearching = filter.length > 0;
        renderFrequentSites();
        renderAllSites(filter, isSearching);
        populateGroupSelects();
    };

    const populateGroupSelects = (selectedGroupId = null) => {
        groupSelect.innerHTML = groups.map(g => `<option value="${g.id}" ${g.id === selectedGroupId ? 'selected' : ''}>${g.name}</option>`).join('');

        document.querySelectorAll('.edit-group-select').forEach(select => {
            const currentGroupId = Number(select.dataset.currentGroup);
            select.innerHTML = groups.map(g => `<option value="${g.id}" ${g.id === currentGroupId ? 'selected' : ''}>${g.name}</option>`).join('');
        });
    };

    const createSiteElement = (site, isFrequentList = false) => {
        const listItem = document.createElement('li');
        listItem.className = 'site-item';
        listItem.dataset.id = site.id;
        listItem.draggable = !isFrequentList;

        const deleteButtonTitle = isFrequentList ? 'Hide from this list' : 'Delete Link';
        listItem.innerHTML = `
            <span class="drag-handle" title="Drag to reorder">${dragIcon}</span>
            <img class="favicon" src="https://www.google.com/s2/favicons?domain=${new URL(site.url).hostname}&sz=32" alt="">
            <div class="site-info">
                <span class="site-name">${site.name}</span>
            </div>
            <div class="edit-form">
                <input type="text" class="edit-name" value="${site.name}" required>
                <input type="url" class="edit-url" value="${site.url}" required>
                <select class="edit-group-select" data-current-group="${site.groupId}"></select>
                <div class="edit-form-buttons">
                    <button class="save-edit-btn">Save</button>
                    <button class="cancel-edit-btn">Cancel</button>
                </div>
            </div>
            <div class="site-item-buttons">
                <button class="icon-btn edit-btn" title="Edit Link">${editIcon}</button>
                <button class="icon-btn delete-btn" title="${deleteButtonTitle}">${trashIcon}</button>
            </div>
        `;
        const dragHandle = listItem.querySelector('.drag-handle');
        if (dragHandle) {
            dragHandle.style.display = isFrequentList ? 'none' : 'block';
        }
        return listItem;
    };

    const renderFrequentSites = () => {
        frequentSitesContainer.innerHTML = '';
        const frequent = [...websites]
            .filter(site => site.clickCount >= 2 && !site.hiddenFromFrequent)
            .sort((a, b) => b.clickCount - a.clickCount)
            .slice(0, 5);

        if (frequent.length > 0) {
            frequentSitesContainer.innerHTML = '<h2>Frequently Used</h2>';
            const list = document.createElement('ul');
            list.id = 'frequentSitesList';
            frequent.forEach(site => list.appendChild(createSiteElement(site, true)));
            frequentSitesContainer.appendChild(list);
        }
    };

    const renderAllSites = (filter = '', isSearching = false) => {
        allSitesContainer.innerHTML = '';
        const filteredSites = websites.filter(site =>
            site.name.toLowerCase().includes(filter) ||
            site.url.toLowerCase().includes(filter)
        );

        if (websites.length === 0) {
            allSitesContainer.style.display = 'none';
            return;
        }
        allSitesContainer.style.display = 'block';

        if (filteredSites.length === 0 && websites.length > 0) {
            allSitesContainer.innerHTML = `<div class="site-item" style="justify-content:center;">No links found.</div>`;
            return;
        }

        groups.forEach(group => {
            const sitesInGroup = filteredSites.filter(site => site.groupId === group.id);
            if (sitesInGroup.length > 0) {
                const groupContainer = document.createElement('div');
                groupContainer.className = 'group-container';
                groupContainer.dataset.groupId = group.id;

                if (group.isCollapsed && !isSearching) {
                    groupContainer.classList.add('collapsed');
                }

                const groupHeader = document.createElement('div');
                groupHeader.className = 'group-header';
                groupHeader.innerHTML = `<span>${group.name} (${sitesInGroup.length})</span><span class="group-toggle-icon">▼</span>`;
                groupContainer.appendChild(groupHeader);

                const siteList = document.createElement('ul');
                siteList.className = 'site-list';
                sitesInGroup.forEach(site => siteList.appendChild(createSiteElement(site, false)));
                groupContainer.appendChild(siteList);

                allSitesContainer.appendChild(groupContainer);
            }
        });
        populateGroupSelects();
    };

    // --- Event Handlers ---
    addCurrentTabBtn.addEventListener('click', () => {
        chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
            if (tabs[0]) {
                const tab = tabs[0];
                const existingSite = websites.find(site => site.url === tab.url);
                if (existingSite) {
                    const groupName = groups.find(g => g.id === existingSite.groupId)?.name || 'a group';
                    showToast(`Link already exists in "${groupName}"!`, 'error');
                    return;
                }
                nameInput.value = tab.title;
                urlInput.value = tab.url;
            }
        });
    });

    addSiteForm.addEventListener('submit', e => {
        e.preventDefault();
        const name = nameInput.value.trim();
        const url = urlInput.value.trim();
        const groupId = Number(groupSelect.value);

        const existingSite = websites.find(site => site.url === url);
        if (existingSite) {
            const groupName = groups.find(g => g.id === existingSite.groupId)?.name || 'a group';
            showToast(`Link already exists in "${groupName}"!`, 'error');
            return;
        }

        if (name && url && groupId) {
            websites.push({ id: Date.now(), name, url, groupId, clickCount: 0, hiddenFromFrequent: false });
            saveData();
            addSiteForm.reset();
            searchInput.value = '';
            showToast('Link Added!');
        }
    });

    addGroupForm.addEventListener('submit', e => {
        e.preventDefault();
        const groupName = groupNameInput.value.trim();
        if (groupName && !groups.some(g => g.name.toLowerCase() === groupName.toLowerCase())) {
            const newGroup = { id: Date.now(), name: groupName, isCollapsed: false };
            groups.push(newGroup);
            saveData();
            addGroupForm.reset();
            populateGroupSelects(newGroup.id);
            showToast('Group Created!');
        }
    });

    searchInput.addEventListener('input', () => {
        const filter = searchInput.value.toLowerCase();
        renderAllSites(filter, filter.length > 0);
    });

    allSitesContainer.addEventListener('click', e => {
        const groupHeader = e.target.closest('.group-header');
        if (groupHeader) {
            const container = groupHeader.closest('.group-container');
            const groupId = Number(container.dataset.groupId);
            const groupIndex = groups.findIndex(g => g.id === groupId);
            if (groupIndex > -1) {
                groups[groupIndex].isCollapsed = !groups[groupIndex].isCollapsed;
                chrome.storage.sync.set({ linkGroups: groups });
                container.classList.toggle('collapsed');
            }
            return;
        }

        const siteItem = e.target.closest('.site-item');
        if (!siteItem) return;

        const id = Number(siteItem.dataset.id);
        const siteIndex = websites.findIndex(s => s.id === id);

        if (e.target.closest('.delete-btn')) {
            if (window.confirm(`Are you sure you want to permanently delete "${websites[siteIndex].name}"?`)) {
                websites = websites.filter(site => site.id !== id);
                saveData();
                showToast('Link Deleted.', 'error');
            }
        } else if (e.target.closest('.edit-btn')) {
            siteItem.querySelector('.site-info').style.display = 'none';
            siteItem.querySelector('.site-item-buttons').style.display = 'none';
            siteItem.querySelector('.edit-form').style.display = 'block';
            populateGroupSelects();
        } else if (e.target.closest('.cancel-edit-btn')) {
            renderAll();
        } else if (e.target.closest('.save-edit-btn')) {
            const newName = siteItem.querySelector('.edit-name').value.trim();
            const newUrl = siteItem.querySelector('.edit-url').value.trim();
            const newGroupId = Number(siteItem.querySelector('.edit-group-select').value);
            if (newName && newUrl && newGroupId) {
                if (siteIndex > -1) {
                    websites[siteIndex].name = newName;
                    websites[siteIndex].url = newUrl;
                    websites[siteIndex].groupId = newGroupId;
                    saveData();
                    showToast('Link Saved!');
                }
            }
        } else if (e.target.closest('.site-info')) {
            if (siteIndex > -1) {
                websites[siteIndex].clickCount++;
                websites[siteIndex].hiddenFromFrequent = false;
                saveData();
                chrome.tabs.create({ url: websites[siteIndex].url });
            }
        }
    });

    frequentSitesContainer.addEventListener('click', e => {
        const siteItem = e.target.closest('.site-item');
        if (!siteItem) return;
        const id = Number(siteItem.dataset.id);
        const siteIndex = websites.findIndex(s => s.id === id);

        if (e.target.closest('.delete-btn')) {
            if (siteIndex > -1) {
                websites[siteIndex].hiddenFromFrequent = true;
                saveData();
            }
        } else if (e.target.closest('.site-info')) {
            if (siteIndex > -1) {
                websites[siteIndex].clickCount++;
                saveData();
                chrome.tabs.create({ url: websites[siteIndex].url });
            }
        }
    });

    themeToggleBtn.addEventListener('click', () => {
        const isLight = document.body.classList.toggle('light-theme');
        const theme = isLight ? 'light' : 'dark';
        themeToggleBtn.innerHTML = isLight ? moonIcon : sunIcon;
        chrome.storage.sync.set({ theme: theme });
    });

    exportBtn.addEventListener('click', () => {
        const dataToExport = { quickLinks: websites, linkGroups: groups };
        const dataStr = JSON.stringify(dataToExport, null, 2);
        const blob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `quick-links-backup-${new Date().toISOString().slice(0,10)}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        showToast('Data Exported!');
    });

    importBtn.addEventListener('click', () => importFile.click());

    importFile.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (event) => {
            try {
                const importedData = JSON.parse(event.target.result);
                if (importedData.quickLinks && importedData.linkGroups) {
                    if (window.confirm("This will overwrite all your current links and groups. Are you sure?")) {
                        websites = importedData.quickLinks;
                        groups = importedData.linkGroups;
                        saveData();
                        showToast('Data Imported Successfully!');
                    }
                } else {
                    showToast("Error: Invalid backup file format.", 'error');
                }
            } catch (error) {
                showToast("Error reading file.", 'error');
            }
        };
        reader.readAsText(file);
        importFile.value = '';
    });

    allSitesContainer.addEventListener('dragstart', e => {
        draggedItem = e.target.closest('.site-item');
        if (draggedItem) setTimeout(() => draggedItem.classList.add('dragging'), 0);
    });

    allSitesContainer.addEventListener('dragend', () => {
        if (draggedItem) setTimeout(() => draggedItem.classList.remove('dragging'), 0);
        draggedItem = null;
    });

    allSitesContainer.addEventListener('dragover', e => {
        e.preventDefault();
        const target = e.target.closest('.site-item');
        if (target && target !== draggedItem) {
            const parentList = target.parentNode;
            const rect = target.getBoundingClientRect();
            const offset = e.clientY - rect.top - rect.height / 2;
            if (offset < 0) {
                parentList.insertBefore(draggedItem, target);
            } else {
                parentList.insertBefore(draggedItem, target.nextSibling);
            }
        }
    });

    allSitesContainer.addEventListener('drop', e => {
        e.preventDefault();
        if (!draggedItem) return;
        const newOrderedIds = [...allSitesContainer.querySelectorAll('.site-item')].map(item => Number(item.dataset.id));
        websites.sort((a, b) => newOrderedIds.indexOf(a.id) - newOrderedIds.indexOf(b.id));
        saveData();
    });

    // --- Initial Load ---
    loadData();
});