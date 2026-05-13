function parseAcceptedTypes(value) {
    if (!value) {
        return [];
    }

    try {
        const parsed = JSON.parse(value);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function parseExistingFiles(value) {
    if (!value) {
        return [];
    }

    try {
        const parsed = JSON.parse(value);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function normalizeExistingFiles(files) {
    return files.map((file) => {
        if (typeof file === 'string') {
            return {
                source: file,
                options: {
                    type: 'local',
                    metadata: {
                        poster: file,
                    },
                },
            };
        }

        if (!file || typeof file !== 'object' || !file.source) {
            return null;
        }

        return {
            source: file.source,
            options: {
                type: file.options?.type || 'local',
                file: file.options?.file || undefined,
                metadata: {
                    ...(file.options?.metadata || {}),
                    poster: file.options?.metadata?.poster || file.source,
                },
            },
        };
    }).filter(Boolean);
}

function resolveFileSourceValue(file) {
    return file.serverId || file.source || null;
}

function initFilePondInputs() {
    if (typeof window.FilePond === 'undefined') {
        return;
    }

    if (!window.__formFilePondPluginsRegistered) {
        window.FilePond.registerPlugin(
            window.FilePondPluginFileValidateType,
            window.FilePondPluginFileValidateSize,
            window.FilePondPluginImagePreview,
            window.FilePondPluginFilePoster
        );
        window.__formFilePondPluginsRegistered = true;
    }

    document.querySelectorAll('input.js-filepond[data-filepond-enabled="1"]').forEach((input) => {
        if (!(input instanceof HTMLInputElement) || input.dataset.filepondInitialized === '1') {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const acceptedTypes = parseAcceptedTypes(input.dataset.filepondAcceptedFileTypes);
        const existingFiles = normalizeExistingFiles(parseExistingFiles(input.dataset.filepondExistingFiles));
        const locale = input.dataset.filepondLocale || 'id';

        const labels = locale === 'id'
            ? {
                labelIdle: '<span class="filepond--label-action">Klik untuk upload</span> atau tarik file ke sini',
                labelFileProcessing: 'Mengupload...',
                labelFileProcessingComplete: 'Upload selesai',
                labelTapToCancel: 'ketuk untuk batal',
                labelTapToUndo: 'ketuk untuk urungkan',
                labelTapToRetry: 'ketuk untuk coba lagi',
            }
            : {};

        const serverConfig = {
            process: {
                url: input.dataset.filepondProcessUrl,
                headers: { 'X-CSRF-TOKEN': csrfToken },
            },
            revert: {
                url: input.dataset.filepondRevertUrl,
                headers: { 'X-CSRF-TOKEN': csrfToken },
            },
        };

        if (input.dataset.filepondLoadUrl) {
            const loadUrl = input.dataset.filepondLoadUrl;
            serverConfig.load = (source, load, error, progress, abort, headers) => {
                const requestUrl = /^(https?:)?\/\//.test(source) || source.startsWith('/')
                    ? source
                    : `${loadUrl}${encodeURIComponent(source)}`;

                const request = new XMLHttpRequest();
                request.open('GET', requestUrl);
                request.responseType = 'blob';
                request.onload = () => {
                    if (request.status >= 200 && request.status < 300) {
                        load(request.response);
                        return;
                    }

                    error('File load failed');
                };
                request.onerror = () => error('File load failed');
                request.onprogress = (event) => {
                    progress(event.lengthComputable, event.loaded, event.total);
                };
                request.send();

                return {
                    abort: () => {
                        request.abort();
                        abort();
                    },
                };
            };
        }

        const originalFieldName = input.name;
        input.dataset.filepondFieldName = originalFieldName;

        window.FilePond.create(input, {
            allowMultiple: input.hasAttribute('multiple'),
            maxFileSize: input.dataset.filepondMaxFileSize || null,
            acceptedFileTypes: acceptedTypes.length ? acceptedTypes : null,
            credits: false,
            files: existingFiles,
            server: serverConfig,
            ...labels,
        });

        input.dataset.filepondInitialized = '1';

        const closestForm = input.closest('form');
        if (closestForm) {
            closestForm.addEventListener('submit', (event) => {
                const currentPond = window.FilePond.find(input);
                if (!currentPond) {
                    return;
                }

                const files = currentPond.getFiles();

                const uploading = files.some((file) => {
                    return file.serverId === null && file.status !== 6 && file.status !== 8;
                });

                if (uploading) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    window.alert('Mohon tunggu hingga upload file selesai sebelum menyimpan.');
                    return;
                }

                const preservedFiles = files
                    .map((file) => resolveFileSourceValue(file))
                    .filter(Boolean);
                const baseName = originalFieldName.replace(/\[\]$/, '');

                closestForm.querySelectorAll(
                    `input[type="hidden"][name="${baseName}[]"], input[type="hidden"][name="${baseName}"]`
                ).forEach((element) => element.remove());

                if (preservedFiles.length === 0) {
                    return;
                }

                preservedFiles.forEach((value) => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = input.hasAttribute('multiple') ? `${baseName}[]` : baseName;
                    hidden.value = value;
                    closestForm.appendChild(hidden);
                });
            }, { capture: true });
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFilePondInputs);
} else {
    initFilePondInputs();
}

document.addEventListener('form:enhance', initFilePondInputs);
