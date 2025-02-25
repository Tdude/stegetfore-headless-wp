// app/page.tsx
import { getPosts } from '../lib/api';

export default async function Home() {
  const posts = await getPosts();

  return (
    <main className="container mx-auto px-4 py-8">
<<<<<<< HEAD
      <h1 className="font-heading text-4xl font-bold tracking-tighter mb-8">Latest Posts, huh?</h1>
=======
      <h1 className="font-heading text-4xl font-bold tracking-tighter mb-8">Latest Posts</h1>
>>>>>>> refs/remotes/origin/main
      <div className="grid gap-6">
        {posts.map((post) => (
          <article key={post.id} className="border p-4 rounded-lg">
            <h2 className="font-heading text-3xl font-bold tracking-tighter mb-8">{post.title.rendered}</h2>
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
