import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import debounce from 'lodash/debounce';

window.Alpine = Alpine;

Alpine.start();

// Função para formatar datas
window.formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

// Função para calcular dias restantes
window.getDaysRemaining = (date) => {
    const today = new Date();
    const expirationDate = new Date(date);
    const diffTime = expirationDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

// Função para formatar números
window.formatNumber = (number) => {
    return new Intl.NumberFormat('pt-BR').format(number);
};

// Função para copiar texto para a área de transferência
window.copyToClipboard = (text) => {
    navigator.clipboard.writeText(text).then(() => {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-primary-500 text-white px-4 py-2 rounded-lg shadow-lg animate-fade-in';
        toast.textContent = 'Copiado para a área de transferência!';
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.remove();
        }, 3000);
    });
};

// Função para mostrar notificações
window.showNotification = (message, type = 'success') => {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-primary-500' : type === 'error' ? 'bg-red-500' : 'bg-yellow-500';
    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg animate-fade-in`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.remove();
    }, 3000);
};

// Função para confirmar ações
window.confirmAction = (message, callback) => {
    if (window.confirm(message)) {
        callback();
    }
};

// Função para debounce
window.debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

// Inicialização do gráfico
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('grafico');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Total', 'Vencendo em Breve', 'Vencidos'],
                datasets: [{
                    label: 'Alimentos',
                    data: canvas.dataset.valores ? JSON.parse(canvas.dataset.valores) : [],
                    backgroundColor: ['#4ade80', '#facc15', '#f87171'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});

// Feedback visual durante carregamento
window.showLoading = () => {
    const loading = document.getElementById('loading-overlay');
    if (loading) loading.classList.remove('hidden');
};

window.hideLoading = () => {
    const loading = document.getElementById('loading-overlay');
    if (loading) loading.classList.add('hidden');
};

// Intercepta todos os forms para mostrar loading
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            showLoading();
        });
    });

    // Busca em tempo real
    const searchInput = document.querySelector('[data-search-alimentos]');
    if (searchInput) {
        const debouncedSearch = debounce(async (value) => {
            try {
                showLoading();
                const response = await fetch(`/alimentos/buscar?q=${encodeURIComponent(value)}`);
                const html = await response.text();
                document.querySelector('#lista-alimentos').innerHTML = html;
            } catch (error) {
                console.error('Erro na busca:', error);
            } finally {
                hideLoading();
            }
        }, 300);

        searchInput.addEventListener('input', (e) => {
            debouncedSearch(e.target.value);
        });
    }

    // Notificações toast
    window.showToast = (message, type = 'success') => {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } transition-opacity duration-300`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };
});
