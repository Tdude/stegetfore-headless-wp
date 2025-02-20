// app/page.tsx
import { getPosts } from '@/lib/api';

export default async function Home() {
  const posts = await getPosts();

  return (
    <main className="container mx-auto px-4 py-8">
      <h1 className="text-4xl font-bold mb-8">Latest Posts</h1>
      <div className="grid gap-6">
        {posts.map((post) => (
          <article key={post.id} className="border p-4 rounded-lg">
            <h2 className="text-2xl font-semibold">{post.title.rendered}</h2>
            <div dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }} />
            {post.featured_image_url && (
              <img
                src={post.featured_image_url}
                alt={post.title.rendered}
                className="mt-4 rounded-lg"
              />
            )}
          </article>
        ))}
      </div>
    </main>
  );
}
