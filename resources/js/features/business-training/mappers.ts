export const mapModulesToForm = (modules: any[]) => {
  return modules
    .sort((a, b) => a.module - b.module)
    .map((m) => {
      if (m.module === 1) {
        return {
          intro_title: m.content[0]?.title || '',
          intro_description: m.content[0]?.description || '',
          advantages: m.content[1]?.advantages || [],
          challenges: m.content[1]?.challenges || [],
          required_mindset: m.content[2]?.required_mindset || [],
        };
      }

      if ([2, 4, 5, 6].includes(m.module)) {
        return {
          items: m.content || [],
        };
      }

      if (m.module === 3) {
        return {
          budget: m.content?.budget || [],
          min_cost: m.content?.estimated_total?.min_cost || null,
          max_cost: m.content?.estimated_total?.max_cost || null,
        };
      }

      return {};
    });
};