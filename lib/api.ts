// lib/api.ts
const API_URL = process.env.NEXT_PUBLIC_WORDPRESS_API_URL;

export async function getPosts() {
  const res = await fetch(`${API_URL}/wp/v2/posts?_embed`);
  if (!res.ok) throw new Error("Failed to fetch posts");
  return res.json();
}
