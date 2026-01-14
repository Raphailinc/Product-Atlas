const state = {
  categories: [],
  products: [],
};

const api = async (path, options = {}) => {
  const response = await fetch(`/api/${path}`, options);
  const data = await response.json().catch(() => ({}));
  if (!response.ok) {
    const message = data.error || `Ошибка запроса (${response.status})`;
    throw new Error(message);
  }
  return data;
};

const toast = (message, variant = 'info') => {
  const el = document.getElementById('toast');
  el.textContent = message;
  el.className = `toast ${variant}`;
  el.hidden = false;
  clearTimeout(el._timeout);
  el._timeout = setTimeout(() => (el.hidden = true), 3000);
};

const renderCategories = (categories) => {
  const categorySelect = document.getElementById('category');
  const filterSelect = document.getElementById('filter-category');
  const reviewSelect = document.getElementById('review-product');

  categorySelect.innerHTML = '<option value="">Выберите категорию</option>';
  filterSelect.innerHTML = '<option value="">Все категории</option>';
  reviewSelect.innerHTML = '';

  categories.forEach((cat) => {
    const option = new Option(cat.name, cat.id);
    categorySelect.appendChild(option);
    filterSelect.appendChild(new Option(cat.name, cat.id));
  });

  state.products.forEach((p) => {
    reviewSelect.appendChild(new Option(p.name, p.id));
  });
};

const renderStats = ({ counts, recent }) => {
  document.querySelector('[data-stat="products"]').textContent = counts.products;
  document.querySelector('[data-stat="categories"]').textContent = counts.categories;
  document.querySelector('[data-stat="reviews"]').textContent = counts.reviews;

  const list = document.querySelector('#recent ul');
  list.innerHTML = '';
  recent.forEach((item) => {
    const li = document.createElement('li');
    li.textContent = `${item.name} · ${item.category_name || 'Без категории'} · ${item.price ?? 0} ₽`;
    list.appendChild(li);
  });
};

const productCard = (product) => {
  const div = document.createElement('div');
  div.className = 'product-card';

  const image = document.createElement('img');
  const placeholder =
    'data:image/svg+xml;utf8,' +
    encodeURIComponent(
      `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 260" fill="none"><defs><linearGradient id="g" x1="0" x2="1" y1="0" y2="1"><stop stop-color="%238cf1d3"/><stop offset="1" stop-color="%234aa6ff"/></linearGradient></defs><rect width="400" height="260" rx="18" fill="%230a0c10"/><rect x="14" y="14" width="372" height="232" rx="14" stroke="url(#g)" stroke-width="3" stroke-dasharray="10 6" opacity="0.4"/><text x="50%" y="52%" dominant-baseline="middle" text-anchor="middle" fill="%238cf1d3" font-family="Space Grotesk, sans-serif" font-size="22">Нет изображения</text></svg>`
    );
  image.src = product.image_url || placeholder;
  image.alt = product.name;
  div.appendChild(image);

  const title = document.createElement('p');
  title.className = 'title';
  title.textContent = product.name;
  div.appendChild(title);

  const desc = document.createElement('p');
  desc.className = 'muted';
  desc.textContent = product.description || 'Без описания';
  div.appendChild(desc);

  const meta = document.createElement('div');
  meta.style.display = 'flex';
  meta.style.gap = '8px';
  meta.style.alignItems = 'center';
  const price = document.createElement('span');
  price.className = 'price';
  price.textContent = `${product.price.toFixed(2)} ₽`;
  meta.appendChild(price);
  const category = document.createElement('span');
  category.className = 'pill';
  category.textContent = product.category || 'Без категории';
  meta.appendChild(category);
  div.appendChild(meta);

  const rating = document.createElement('div');
  rating.className = 'rating';
  rating.innerHTML = `★ ${product.avg_rating.toFixed(1)} · ${product.review_count} отзыв(ов)`;
  div.appendChild(rating);

  return div;
};

const renderProducts = (products) => {
  const container = document.getElementById('products');
  container.innerHTML = '';
  if (!products.length) {
    container.innerHTML = '<p class="muted">Ничего не найдено.</p>';
    return;
  }
  products.forEach((p) => container.appendChild(productCard(p)));

  const reviewSelect = document.getElementById('review-product');
  reviewSelect.innerHTML = '';
  products.forEach((p) => reviewSelect.appendChild(new Option(p.name, p.id)));
};

const loadAll = async (params = {}) => {
  const query = new URLSearchParams(params);
  const [products, categories, stats] = await Promise.all([
    api(`products.php?${query.toString()}`),
    api('categories.php'),
    api('stats.php'),
  ]);
  state.products = products.products || [];
  state.categories = categories.categories || [];
  renderProducts(state.products);
  renderCategories(state.categories);
  renderStats(stats);
};

const handleProductSubmit = async (event) => {
  event.preventDefault();
  const form = event.target;
  const fd = new FormData(form);
  try {
    const { product } = await api('products.php', { method: 'POST', body: fd });
    toast('Товар сохранён', 'success');
    form.reset();
    await loadAll();
    document.getElementById('api-preview').textContent = JSON.stringify(product, null, 2).slice(0, 260) + '...';
  } catch (e) {
    toast(e.message, 'danger');
  }
};

const handleReviewSubmit = async (event) => {
  event.preventDefault();
  const form = event.target;
  const fd = new FormData(form);
  try {
    await api('reviews.php', { method: 'POST', body: fd });
    toast('Отзыв добавлен', 'success');
    form.reset();
    await loadAll();
  } catch (e) {
    toast(e.message, 'danger');
  }
};

const debounce = (fn, delay = 300) => {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), delay);
  };
};

const attachEvents = () => {
  document.getElementById('product-form').addEventListener('submit', handleProductSubmit);
  document.getElementById('review-form').addEventListener('submit', handleReviewSubmit);

  const search = document.getElementById('search');
  const filterCategory = document.getElementById('filter-category');
  const refresh = document.getElementById('refresh');

  const applyFilters = debounce(async () => {
    const params = {};
    if (search.value.trim()) params.q = search.value.trim();
    if (filterCategory.value) params.category_id = filterCategory.value;
    await loadAll(params);
  }, 250);

  search.addEventListener('input', applyFilters);
  filterCategory.addEventListener('change', applyFilters);
  refresh.addEventListener('click', () => loadAll());
};

window.addEventListener('DOMContentLoaded', async () => {
  attachEvents();
  try {
    await loadAll();
  } catch (e) {
    toast(e.message, 'danger');
  }
});
