document.addEventListener('DOMContentLoaded', () => {
  const feed = document.getElementById('feed-okinawa');
  if (!feed) return;

  fetch('instagram-feed.php', { headers: { Accept: 'application/json' } })
    .then((response) => {
      if (!response.ok) throw new Error('Instagram feed request failed');
      return response.json();
    })
    .then((payload) => {
      const profile = document.getElementById('profile-okinawa');
      if (profile && payload.profile_picture_url) profile.src = payload.profile_picture_url;
      feed.replaceChildren();

      (payload.data || []).forEach((post) => {
        const slide = document.createElement('div');
        slide.className = 'swiper-slide';
        const link = document.createElement('a');
        link.className = 'instagram-post';
        link.href = post.permalink;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.setAttribute('aria-label', 'Instagramの投稿を見る');
        const image = document.createElement('img');
        image.loading = 'lazy';
        image.alt = '';
        image.src = post.media_type === 'VIDEO' && post.thumbnail_url ? post.thumbnail_url : post.media_url;
        link.appendChild(image);

        const info = document.createElement('span');
        info.className = 'instagram-info';
        const likes = document.createElement('span');
        likes.textContent = `♡ ${Number(post.like_count || 0).toLocaleString('ja-JP')}件`;
        const date = document.createElement('time');
        if (post.timestamp) {
          const postedAt = new Date(post.timestamp);
          if (!Number.isNaN(postedAt.getTime())) {
            date.dateTime = postedAt.toISOString();
            date.textContent = new Intl.DateTimeFormat('ja-JP', {
              year: 'numeric', month: '2-digit', day: '2-digit',
            }).format(postedAt).replaceAll('/', '.');
          }
        }
        info.append(likes, date);
        slide.append(link, info);
        feed.appendChild(slide);
      });

      if (!feed.children.length) throw new Error('Instagram feed is empty');
      new Swiper('.swiper-okinawa', {
        slidesPerView: 'auto',
        spaceBetween: 14,
        navigation: {
          nextEl: '.swiper-button-okinawa-next',
          prevEl: '.swiper-button-okinawa-prev',
        },
      });
    })
    .catch(() => {
      feed.innerHTML = '<p class="instagram-error">Instagramの投稿は現在読み込めません。</p>';
    });
});
