import { Header } from "./components/Header";
import { FlowDiagram } from "./components/FlowDiagram";
import { InventorySection } from "./components/InventorySection";
import { AnalyticsSection } from "./components/AnalyticsSection";
import { AutoOrderSection } from "./components/AutoOrderSection";
import { CompatibilitySection } from "./components/CompatibilitySection";

export default function App() {
  return (
    <div className="min-h-screen bg-gray-50">
      <Header />
      
      {/* Hero Section */}
      <main className="flex-1 flex items-center justify-center py-20">
        <div className="text-center">
          <FlowDiagram />
        </div>
      </main>

      {/* Inventory Section */}
      <InventorySection />

      {/* Analytics Section */}
      <AnalyticsSection />

      {/* Auto Order Section */}
      <AutoOrderSection />

      {/* Compatibility Section */}
      <CompatibilitySection />
    </div>
  );
}