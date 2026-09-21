import 'package:flutter/material.dart';

import '../../shared/widgets/componentes.dart';
import '../dashboard/presentation/dashboard_page.dart';
import '../obras/presentation/obras_page.dart';
import '../perfil/presentation/perfil_page.dart';

/// Estrutura principal após o login: Dashboard · Obras · Perfil.
/// IndexedStack mantém o estado de cada aba (busca, página carregada...).
class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _aba = 0;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: const InstitucionalAppBar(),
      body: IndexedStack(
        index: _aba,
        children: const [DashboardPage(), ObrasPage(), PerfilPage()],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _aba,
        onDestinationSelected: (i) => setState(() => _aba = i),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.dashboard_outlined),
            selectedIcon: Icon(Icons.dashboard),
            label: 'Dashboard',
          ),
          NavigationDestination(
            icon: Icon(Icons.apartment_outlined),
            selectedIcon: Icon(Icons.apartment),
            label: 'Obras',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outline),
            selectedIcon: Icon(Icons.person),
            label: 'Perfil',
          ),
        ],
      ),
    );
  }
}
