<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dark Landing Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'dark-blue': '#0f172a',
                        'darker-blue': '#020617',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-darker-blue via-dark-blue to-slate-900 min-h-screen text-white">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-20 left-20 w-32 h-32 border border-blue-400/20 rotate-45"></div>
        <div class="absolute top-40 right-32 w-24 h-24 border border-purple-400/20 rotate-12"></div>
        <div class="absolute bottom-32 left-1/4 w-16 h-16 border border-cyan-400/20 rotate-45"></div>
        <div class="absolute bottom-20 right-20 w-20 h-20 border border-blue-400/20 rotate-12"></div>
    </div>

    <div class="relative z-10 container mx-auto px-6 py-16 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-16">
            <div class="flex items-center justify-center mb-8">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold">Chat Hub</h1>
            </div>

            <h2 class="text-5xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-white via-blue-100 to-purple-200 bg-clip-text text-transparent">
                Connect & Communicate
            </h2>

            <p class="text-xl text-gray-300 mb-12 max-w-2xl mx-auto leading-relaxed">
                Experience seamless conversations with our modern chat platform.
                Fast, beautiful, and completely intuitive.
            </p>

            <!-- Main CTA Button -->
            <a href="/chats"
               class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25 group">
                <span class="mr-3">Start Chatting</span>
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>

        <!-- Blog Posts Card -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700/50 rounded-2xl p-8 shadow-2xl">
                <div class="flex items-center mb-6">
                    <div class="w-8 h-8 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white">Latest Blog Posts</h3>
                </div>

                <div class="space-y-4">
                    <a href="https://blog.debjit.in/curl-rag-part-1-fundamentals-and-setup"  target="_blank"
                       class="block p-4 rounded-xl bg-slate-700/30 hover:bg-slate-700/50 border border-slate-600/30 hover:border-slate-500/50 transition-all duration-300 group">
                        <h4 class="font-semibold text-white group-hover:text-blue-300 transition-colors duration-300 mb-2">
                            Part 1: Understanding RAG Fundamentals & Setting Up Your Environment
                        </h4>
                        <p class="text-gray-400 text-sm">Learn the basics of RAG Tutorial Using Open Source LLMs, Vector Database, and Curl Part 1</p>
                    </a>

                    <a href="https://blog.debjit.in/part-2-populating-your-vector-database-embedding-and-uploading-data"  target="_blank"
                       class="block p-4 rounded-xl bg-slate-700/30 hover:bg-slate-700/50 border border-slate-600/30 hover:border-slate-500/50 transition-all duration-300 group">
                        <h4 class="font-semibold text-white group-hover:text-blue-300 transition-colors duration-300 mb-2">
                            Security Best Practices for Online Communication
                        </h4>
                        <p class="text-gray-400 text-sm">RAG Tutorial Using Open Source LLMs, Vector Database, and Curl Part 2</p>
                    </a>

                    <a href="https://blog.debjit.in/part-3-searching-your-knowledge-querying-the-vector-database"  target="_blank"
                       class="block p-4 rounded-xl bg-slate-700/30 hover:bg-slate-700/50 border border-slate-600/30 hover:border-slate-500/50 transition-all duration-300 group">
                        <h4 class="font-semibold text-white group-hover:text-blue-300 transition-colors duration-300 mb-2">
                            10 Productivity Tips for Team Communication
                        </h4>
                        <p class="text-gray-400 text-sm">RAG Tutorial Using Open Source LLMs, Vector Database, and Curl Part 3</p>
                    </a>

                    <a href="https://blog.debjit.in/part-4-generating-answers-with-llms-and-enhancing-your-rag-system"  target="_blank"
                       class="block p-4 rounded-xl bg-slate-700/30 hover:bg-slate-700/50 border border-slate-600/30 hover:border-slate-500/50 transition-all duration-300 group">
                        <h4 class="font-semibold text-white group-hover:text-blue-300 transition-colors duration-300 mb-2">
                            The Future of Messaging: AI and Beyond
                        </h4>
                        <p class="text-gray-400 text-sm">RAG Tutorial Using Open Source LLMs, Vector Database, and Curl Part 4</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
