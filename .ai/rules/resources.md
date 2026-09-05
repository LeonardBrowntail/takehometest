---
paths:
  - 'app/Http/Resources/**'
---

# Resources

## JSON:API relationships must not use whenLoaded()
Laravel 13 JsonApiResource relationships must be declared via the $relationships property (or toRelationships returning class-string/null resolvers). Never return $this->whenLoaded(...) from toRelationships — the JSON:API serializer calls RelationResolver::__construct(string, MissingValue) and 500s with a TypeError. Since the framework loadMissing()s declared relations automatically, plain `['owner', 'ledgers']` is enough.
