import { Link } from "wouter";

export default function NotFound() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-slate-950 text-white px-4">
      <div className="text-center space-y-4">
        <div className="text-8xl font-bold text-indigo-500 mb-2">404</div>
        <h1 className="text-2xl font-semibold">Page Not Found</h1>
        <p className="text-slate-400 max-w-md">
          The page you're looking for doesn't exist or has been moved.
        </p>
        <Link href="/">
          <button className="mt-6 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 rounded-lg font-medium transition-colors inline-block">
            ← Back to Home
          </button>
        </Link>
      </div>
    </div>
  );
}
