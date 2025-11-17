import { useState, useEffect } from 'react';
import { Search, Filter, Calendar, User, X } from 'lucide-react';

const SearchAndFilter = ({
  type = 'appointment',
  onSearch,
  onFilterChange,
  showDateRange = true,
  showStatusFilter = true,
  showDoctorFilter = false,
  showOwnerFilter = false,
  showPetFilter = false,
  doctors = [],
  statusOptions = [
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'confirmada', label: 'Confirmada' },
    { value: 'completada', label: 'Completada' },
    { value: 'cancelada', label: 'Cancelada' }
  ]
}) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [filters, setFilters] = useState({
    fecha_inicio: '',
    fecha_fin: '',
    estado: '',
    id_doctor: '',
    nombre_propietario: '',
    nombre_mascota: '',
    ordenar_por: 'fecha_cita',
    orden: 'desc'
  });
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    // Aplicar búsqueda con un pequeño retraso para evitar múltiples llamadas
    const delayDebounce = setTimeout(() => {
      if (searchTerm.trim() !== '') {
        onSearch(searchTerm);
      } else if (searchTerm === '') {
        onSearch('');
      }
    }, 500);

    return () => clearTimeout(delayDebounce);
  }, [searchTerm, onSearch]);

  const handleFilterChange = (e) => {
    const { name, value } = e.target;
    const newFilters = {
      ...filters,
      [name]: value
    };
    
    setFilters(newFilters);
    onFilterChange(newFilters);
  };

  const resetFilters = () => {
    const resetFilters = {
      fecha_inicio: '',
      fecha_fin: '',
      estado: '',
      id_doctor: '',
      nombre_propietario: '',
      nombre_mascota: '',
      ordenar_por: 'fecha_cita',
      orden: 'desc'
    };
    
    setFilters(resetFilters);
    setSearchTerm('');
    onFilterChange(resetFilters);
  };

  return (
    <div className="search-filter-container">
      <div className="search-bar">
        <div className="search-input-container">
          <Search className="search-icon" size={18} />
          <input
            type="text"
            placeholder={`Buscar ${type === 'appointment' ? 'citas' : type === 'report' ? 'reportes' : 'clientes'}...`}
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="search-input"
          />
        </div>
        
        <button 
          className={`filter-toggle ${showFilters ? 'active' : ''}`}
          onClick={() => setShowFilters(!showFilters)}
        >
          <Filter size={18} />
          <span>Filtros</span>
        </button>
      </div>

      {showFilters && (
        <div className="filters-panel">
          <div className="filters-header">
            <h4>Filtros de búsqueda</h4>
            <button onClick={resetFilters} className="reset-filters">
              <X size={16} /> Limpiar filtros
            </button>
          </div>
          
          <div className="filters-grid">
            {showDateRange && (
              <>
                <div className="form-group">
                  <label htmlFor="fecha_inicio">
                    <Calendar size={16} /> Fecha desde
                  </label>
                  <input
                    type="date"
                    id="fecha_inicio"
                    name="fecha_inicio"
                    value={filters.fecha_inicio}
                    onChange={handleFilterChange}
                    className="form-control"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="fecha_fin">
                    <Calendar size={16} /> Fecha hasta
                  </label>
                  <input
                    type="date"
                    id="fecha_fin"
                    name="fecha_fin"
                    value={filters.fecha_fin}
                    onChange={handleFilterChange}
                    className="form-control"
                    min={filters.fecha_inicio || ''}
                  />
                </div>
              </>
            )}

            {showStatusFilter && (
              <div className="form-group">
                <label htmlFor="estado">Estado</label>
                <select
                  id="estado"
                  name="estado"
                  value={filters.estado}
                  onChange={handleFilterChange}
                  className="form-control"
                >
                  <option value="">Todos los estados</option>
                  {statusOptions.map((option) => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
              </div>
            )}

            {showDoctorFilter && doctors.length > 0 && (
              <div className="form-group">
                <label htmlFor="id_doctor">Doctor</label>
                <select
                  id="id_doctor"
                  name="id_doctor"
                  value={filters.id_doctor}
                  onChange={handleFilterChange}
                  className="form-control"
                >
                  <option value="">Todos los doctores</option>
                  {doctors.map((doctor) => (
                    <option key={doctor.id_doctores} value={doctor.id_doctores}>
                      {doctor.nombre}
                    </option>
                  ))}
                </select>
              </div>
            )}

            {showOwnerFilter && (
              <div className="form-group">
                <label htmlFor="nombre_propietario">
                  <User size={16} /> Propietario
                </label>
                <input
                  type="text"
                  id="nombre_propietario"
                  name="nombre_propietario"
                  placeholder="Buscar por propietario"
                  value={filters.nombre_propietario}
                  onChange={handleFilterChange}
                  className="form-control"
                />
              </div>
            )}

            {showPetFilter && (
              <div className="form-group">
                <label htmlFor="nombre_mascota">
                  <PawPrint size={16} /> Mascota
                </label>
                <input
                  type="text"
                  id="nombre_mascota"
                  name="nombre_mascota"
                  placeholder="Buscar por mascota"
                  value={filters.nombre_mascota}
                  onChange={handleFilterChange}
                  className="form-control"
                />
              </div>
            )}

            <div className="form-group">
              <label htmlFor="ordenar_por">Ordenar por</label>
              <select
                id="ordenar_por"
                name="ordenar_por"
                value={filters.ordenar_por}
                onChange={handleFilterChange}
                className="form-control"
              >
                <option value="fecha_cita">Fecha de cita</option>
                <option value="fecha_creacion">Fecha de creación</option>
                <option value="estado">Estado</option>
              </select>
            </div>

            <div className="form-group">
              <label htmlFor="orden">Orden</label>
              <select
                id="orden"
                name="orden"
                value={filters.orden}
                onChange={handleFilterChange}
                className="form-control"
              >
                <option value="asc">Ascendente</option>
                <option value="desc">Descendente</option>
              </select>
            </div>
          </div>
        </div>
      )}

      <style jsx>{`
        .search-filter-container {
          margin-bottom: 1.5rem;
        }
        
        .search-bar {
          display: flex;
          gap: 1rem;
          margin-bottom: 1rem;
        }
        
        .search-input-container {
          position: relative;
          flex: 1;
        }
        
        .search-icon {
          position: absolute;
          left: 12px;
          top: 50%;
          transform: translateY(-50%);
          color: #6b7280;
        }
        
        .search-input {
          width: 100%;
          padding: 0.5rem 1rem 0.5rem 2.5rem;
          border: 1px solid #d1d5db;
          border-radius: 0.375rem;
          font-size: 0.875rem;
          transition: border-color 0.2s;
        }
        
        .search-input:focus {
          outline: none;
          border-color: #3b82f6;
          box-shadow: 0 0 0 1px #3b82f6;
        }
        
        .filter-toggle {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          padding: 0.5rem 1rem;
          background-color: #f3f4f6;
          border: 1px solid #d1d5db;
          border-radius: 0.375rem;
          font-size: 0.875rem;
          cursor: pointer;
          transition: all 0.2s;
        }
        
        .filter-toggle:hover, .filter-toggle.active {
          background-color: #e5e7eb;
        }
        
        .filters-panel {
          background-color: #f9fafb;
          border: 1px solid #e5e7eb;
          border-radius: 0.5rem;
          padding: 1.25rem;
          margin-top: 1rem;
        }
        
        .filters-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 1rem;
        }
        
        .filters-header h4 {
          margin: 0;
          font-size: 1rem;
          font-weight: 600;
          color: #111827;
        }
        
        .reset-filters {
          display: flex;
          align-items: center;
          gap: 0.25rem;
          background: none;
          border: none;
          color: #3b82f6;
          font-size: 0.875rem;
          cursor: pointer;
          padding: 0.25rem 0.5rem;
          border-radius: 0.25rem;
        }
        
        .reset-filters:hover {
          background-color: #eff6ff;
        }
        
        .filters-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
          gap: 1rem;
        }
        
        .form-group {
          display: flex;
          flex-direction: column;
          gap: 0.375rem;
        }
        
        .form-group label {
          font-size: 0.875rem;
          font-weight: 500;
          color: #4b5563;
          display: flex;
          align-items: center;
          gap: 0.375rem;
        }
        
        .form-control {
          padding: 0.5rem 0.75rem;
          border: 1px solid #d1d5db;
          border-radius: 0.375rem;
          font-size: 0.875rem;
          transition: border-color 0.2s;
        }
        
        .form-control:focus {
          outline: none;
          border-color: #3b82f6;
          box-shadow: 0 0 0 1px #3b82f6;
        }
        
        @media (max-width: 640px) {
          .filters-grid {
            grid-template-columns: 1fr;
          }
        }
      `}</style>
    </div>
  );
};

export default SearchAndFilter;
