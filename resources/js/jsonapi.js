/**
 * JSON:API response normalization helpers.
 *
 * Converts JSON:API format ({ type, id, attributes, relationships } + included)
 * into flat objects the Vue pages can use directly.
 */

function findIncluded(included, type, id) {
    if (!included) return null;
    const item = included.find(i => i.type === type && String(i.id) === String(id));
    if (!item) return null;
    return { id: Number(item.id), ...item.attributes };
}

function resolveRelationship(rel, included) {
    if (!rel || !rel.data) return null;

    if (Array.isArray(rel.data)) {
        return rel.data.map(ref => findIncluded(included, ref.type, ref.id)).filter(Boolean);
    }

    return findIncluded(included, rel.data.type, rel.data.id);
}

/**
 * Parse a single JSON:API resource into a flat object.
 */
export function parseResource(resource, included = []) {
    if (!resource) return null;

    const obj = { id: Number(resource.id), ...resource.attributes };

    if (resource.relationships) {
        for (const [key, rel] of Object.entries(resource.relationships)) {
            obj[key] = resolveRelationship(rel, included);
        }
    }

    return obj;
}

/**
 * Parse a JSON:API collection response (paginated).
 * Input: full axios response.data ({ data: [...], included: [...], meta: {...} })
 * Returns: { items: [...flat objects], meta: {...} }
 */
export function parseCollection(responseData) {
    const included = responseData.included || [];
    const items = (responseData.data || []).map(resource => parseResource(resource, included));
    return { items, meta: responseData.meta || {} };
}

/**
 * Parse a single-resource JSON:API response.
 * Input: full axios response.data ({ data: {...}, included: [...] })
 * Returns: flat object
 */
export function parseSingle(responseData) {
    return parseResource(responseData.data, responseData.included || []);
}
