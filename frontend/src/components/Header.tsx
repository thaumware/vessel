import { Button } from "./ui/button";

export function Header() {
  return (
    <header className="w-full bg-white border-b border-gray-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Logo */}
          <div className="flex items-center">
            <div className="w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-600 rounded-lg flex items-center justify-center">
              <span className="text-white font-bold text-xl">V</span>
            </div>
          </div>

          {/* Navigation */}
          <nav className="hidden md:flex space-x-8">
            <a href="#" className="text-gray-600 hover:text-gray-900 transition-colors">
              Inventario
            </a>
            <a href="#" className="text-gray-600 hover:text-gray-900 transition-colors">
              Vista general
            </a>
            <a href="#" className="text-gray-600 hover:text-gray-900 transition-colors">
              Funciones
            </a>
            <a href="#" className="text-gray-600 hover:text-gray-900 transition-colors">
              Hardware
            </a>
          </nav>

          {/* Action buttons */}
          <div className="flex items-center space-x-4">
            <Button variant="ghost" className="text-gray-600 hover:text-gray-900">
              Iniciar sesión
            </Button>
            <Button className="bg-purple-600 hover:bg-purple-700 text-white">
              Pruébalo gratis
            </Button>
          </div>
        </div>
      </div>
    </header>
  );
}